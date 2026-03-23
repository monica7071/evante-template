/**
 * Report Service for handling report data and API calls
 */
class ReportService {
    constructor() {
        this.baseUrl = '/report';
        this.currentFilters = {
            year: new Date().getFullYear(),
            month: null,
            status: null,
            employee_id: null
        };
    }

    /**
     * Set filters for report data
     */
    setFilters(filters) {
        this.currentFilters = { ...this.currentFilters, ...filters };
    }

    /**
     * Get all report data
     */
    async getReportData() {
        try {
            const response = await fetch(`${this.baseUrl}/data?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch report data');
            }
        } catch (error) {
            console.error('Error fetching report data:', error);
            throw error;
        }
    }

    /**
     * Get team performance data
     */
    async getTeamPerformance() {
        try {
            const response = await fetch(`${this.baseUrl}/team-performance?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch team performance');
            }
        } catch (error) {
            console.error('Error fetching team performance:', error);
            throw error;
        }
    }

    /**
     * Get sale performance data
     */
    async getSalePerformance() {
        try {
            const response = await fetch(`${this.baseUrl}/sale-performance?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch sale performance');
            }
        } catch (error) {
            console.error('Error fetching sale performance:', error);
            throw error;
        }
    }

    /**
     * Get top performers data
     */
    async getTopPerformers(limit = 5) {
        try {
            const params = { ...this.currentFilters, limit };
            const response = await fetch(`${this.baseUrl}/top-performers?${new URLSearchParams(params)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch top performers');
            }
        } catch (error) {
            console.error('Error fetching top performers:', error);
            throw error;
        }
    }

    /**
     * Get lead source data
     */
    async getLeadSourceData() {
        try {
            const response = await fetch(`${this.baseUrl}/lead-source?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch lead source data');
            }
        } catch (error) {
            console.error('Error fetching lead source data:', error);
            throw error;
        }
    }

    /**
     * Get monthly production data
     */
    async getMonthlyProduction(year = null) {
        try {
            const params = { ...this.currentFilters };
            if (year) params.year = year;

            const response = await fetch(`${this.baseUrl}/monthly-production?${new URLSearchParams(params)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch monthly production');
            }
        } catch (error) {
            console.error('Error fetching monthly production:', error);
            throw error;
        }
    }

    /**
     * Get status summary data
     */
    async getStatusSummary() {
        try {
            const response = await fetch(`${this.baseUrl}/status-summary?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to fetch status summary');
            }
        } catch (error) {
            console.error('Error fetching status summary:', error);
            throw error;
        }
    }

    /**
     * Export report to PDF
     */
    async exportPdf() {
        try {
            const response = await fetch(`${this.baseUrl}/export-pdf?${new URLSearchParams(this.currentFilters)}`);
            const data = await response.json();

            if (data.success) {
                // Handle PDF download
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to export PDF');
            }
        } catch (error) {
            console.error('Error exporting PDF:', error);
            throw error;
        }
    }

    /**
     * Update charts with new data
     */
    updateCharts(chartData) {
        // Update Team Chart
        if (window.teamChart && chartData.team_performance) {
            const teamData = {
                labels: chartData.team_performance.map(item => item.employee_name),
                values: chartData.team_performance.map(item => item.total_production),
                colors: ['#F59E0B', '#FDBA74', '#ECCC68', '#E5E7EB', '#D1D5DB']
            };

            window.teamChart.data.labels = teamData.labels;
            window.teamChart.data.datasets[0].data = teamData.values;
            window.teamChart.data.datasets[0].backgroundColor = teamData.colors.slice(0, teamData.labels.length);
            window.teamChart.update();
        }

        // Update Sale Chart
        if (window.saleChart && chartData.top_performers) {
            const saleData = {
                labels: chartData.top_performers.map(item => item.employee_name),
                values: chartData.top_performers.map(item => item.total_production),
                colors: ['#F59E0B', '#FDBA74', '#ECCC68', '#E5E7EB', '#D1D5DB']
            };

            window.saleChart.data.labels = saleData.labels;
            window.saleChart.data.datasets[0].data = saleData.values;
            window.saleChart.data.datasets[0].backgroundColor = saleData.colors.slice(0, saleData.labels.length);
            window.saleChart.update();
        }

        // Update Production Chart
        if (window.productionChart && chartData.monthly_production) {
            window.productionChart.data.datasets[0].data = chartData.monthly_production.data;
            window.productionChart.update();
        }
    }

    /**
     * Update UI with new data
     */
    updateUI(chartData) {
        // Update totals
        const totalProduction = chartData.team_performance?.reduce((sum, item) => sum + item.total_production, 0) || 0;
        const totalRequests = chartData.team_performance?.reduce((sum, item) => sum + item.request_count, 0) || 0;

        // Update total production displays
        document.querySelectorAll('.total-production').forEach(element => {
            element.textContent = `฿${totalProduction.toLocaleString()}`;
        });

        // Update team members list
        this.updateTeamMembers(chartData.team_performance);

        // Update top performers list
        this.updateTopPerformers(chartData.top_performers);

        // Update lead source data
        this.updateLeadSource(chartData.lead_source);

        // Update charts
        this.updateCharts(chartData);
    }

    /**
     * Update team members section
     */
    updateTeamMembers(teamData) {
        const container = document.querySelector('.team-members-container');
        if (!container || !teamData) return;

        const colors = ['bg-yellow-400', 'bg-orange-300', 'bg-amber-300', 'bg-gray-300'];

        container.innerHTML = teamData.map((member, index) => `
            <div class="flex items-center">
                <div class="w-1 h-12 ${colors[index % colors.length]} mr-3"></div>
                <div>
                    <div class="text-sm font-medium">${member.employee_name}</div>
                    <div class="text-sm font-medium">฿${member.total_production.toLocaleString()}</div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Update top performers section
     */
    updateTopPerformers(performers) {
        const container = document.querySelector('.top-performers-container');
        if (!container || !performers) return;

        container.innerHTML = performers.map((performer, index) => `
            <div class="flex items-center justify-between py-1">
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/${index % 2 == 0 ? 'women' : 'men'}/${32 + index * 12}.jpg"
                         alt="${performer.employee_name}" class="w-8 h-8 rounded-full mr-3">
                    <span class="text-gray-700">${performer.employee_name}</span>
                </div>
                <div class="font-medium text-gray-800">฿${performer.total_production.toLocaleString()}</div>
            </div>
        `).join('');
    }

    /**
     * Update lead source section
     */
    updateLeadSource(leadSourceData) {
        const container = document.querySelector('.lead-source-container');
        if (!container || !leadSourceData) return;

        const maxCount = Math.max(...leadSourceData.map(item => item.count));

        container.innerHTML = leadSourceData.map(source => `
            <div class="flex flex-col">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700">${source.lead_source}</span>
                    <span class="text-sm font-medium">${source.count}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-amber-400 h-2 rounded-full" style="width: ${(source.count / maxCount) * 100}%"></div>
                </div>
            </div>
        `).join('');
    }
}

// Initialize report service
window.reportService = new ReportService();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReportService;
}
