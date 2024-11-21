@extends('layouts.app')

@section('title', 'Background Jobs Dashboard')

@section('content')
<div x-data="jobsDashboard()" x-init="init()">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Background Jobs Dashboard</h1>
        <div class="flex items-center space-x-4">
            <span x-show="loading" class="text-sm text-gray-500">Loading...</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" x-model="autoRefresh" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-500">Auto-refresh (<span x-text="nextRefreshIn"></span>s)</span>
            </label>
            <button 
                @click="fetchData" 
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400"
                :disabled="loading"
            >
                Refresh Now
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Recent Jobs</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class/Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="!jobs.length">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No jobs found</td>
                            </tr>
                        </template>
                        <template x-for="job in sortedJobs" :key="`${job.job_id}-${job.timestamp}`">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900" x-text="job.job_id"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span x-text="job.class"></span>:<span x-text="job.method"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 py-1 text-xs rounded-full inline-flex items-center"
                                        :class="{
                                            'bg-green-100 text-green-800': job.status === 'success',
                                            'bg-yellow-100 text-yellow-800': job.status === 'running',
                                            'bg-red-100 text-red-800': job.status === 'failed'
                                        }"
                                        x-text="job.status"
                                    ></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(job.timestamp)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Error Log</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class/Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-if="!errors.length">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No errors found</td>
                            </tr>
                        </template>
                        <template x-for="error in sortedErrors" :key="`${error.job_id}-${error.timestamp}`">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900" x-text="error.job_id"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span x-text="error.class"></span>:<span x-text="error.method"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 py-1 text-xs rounded-full inline-flex items-center bg-red-100 text-red-800"
                                        x-text="error.status"
                                    ></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(error.timestamp)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function jobsDashboard() {
    return {
        jobs: [],
        errors: [],
        loading: false,
        autoRefresh: true,
        refreshInterval: 5,
        nextRefreshIn: 5,
        timer: null,

        get sortedJobs() {
            return [...this.jobs].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        },

        get sortedErrors() {
            return [...this.errors].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        },

        init() {
            this.fetchData();
            this.startRefreshTimer();
        },

        async fetchData() {
            this.loading = true;
            try {
                const response = await fetch('/api/background-jobs');
                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();
                this.jobs = data.jobs || [];
                this.errors = data.errors || [];
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                this.loading = false;
            }
        },

        startRefreshTimer() {
            if (this.timer) clearInterval(this.timer);
            
            this.timer = setInterval(() => {
                if (this.autoRefresh) {
                    this.nextRefreshIn--;
                    if (this.nextRefreshIn <= 0) {
                        this.fetchData();
                        this.nextRefreshIn = this.refreshInterval;
                    }
                }
            }, 1000);
        },

        formatDate(timestamp) {
            if (!timestamp) return '';
            try {
                return new Date(timestamp).toLocaleString();
            } catch (e) {
                return timestamp;
            }
        }
    }
}
</script>
@endpush