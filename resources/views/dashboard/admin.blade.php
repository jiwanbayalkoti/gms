@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.4;
    }
</style>
@endpush

@section('content')
<div class="row">
    <!-- Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card members">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Members</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalMembers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users text-primary stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card trainers">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Trainers</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTrainers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-tie text-info stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card classes">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Classes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeClasses }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dumbbell text-success stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card income">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Income</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($monthlyIncome ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-up text-success stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary Row -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Income (This Month)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($monthlyIncome ?? 0, 2) }}</div>
                        <div class="text-xs text-muted mt-1">Yearly: ${{ number_format($yearlyIncome ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Expenses (This Month)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($monthlyExpenses ?? 0, 2) }}</div>
                        <div class="text-xs text-muted mt-1">Yearly: ${{ number_format($yearlyExpenses ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-down fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Net Profit (This Month)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800 {{ ($monthlyNetProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($monthlyNetProfit ?? 0, 2) }}
                        </div>
                        <div class="text-xs text-muted mt-1">Yearly: ${{ number_format($yearlyNetProfit ?? 0, 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x {{ ($monthlyNetProfit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Financial Chart (Income vs Expenses) -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Financial Overview (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend Chart -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Trend (Last 12 Months)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Income (Payments) -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">Recent Income (Payments)</h6>
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-success">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments ?? [] as $payment)
                            <tr>
                                <td>{{ $payment->member->name ?? 'N/A' }}</td>
                                <td class="text-success font-weight-bold">+${{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No recent payments</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses (Salary Payments) -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-danger">Recent Expenses (Salary Payments)</h6>
                <a href="{{ route('salary-payments.index') }}" class="btn btn-sm btn-danger">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSalaryPayments ?? [] as $salaryPayment)
                            <tr>
                                <td>{{ $salaryPayment->employee->name ?? 'N/A' }}</td>
                                <td class="text-danger font-weight-bold">-${{ number_format($salaryPayment->net_amount, 2) }}</td>
                                <td>{{ $salaryPayment->payment_date ? $salaryPayment->payment_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No recent salary payments</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Attendance Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Attendance (Last 7 Days)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upcoming Classes -->
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Upcoming Classes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Trainer</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingClasses as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->trainer->name }}</td>
                                <td>
                                    {{ $class->start_time->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $class->start_time->format('h:i A') }} - {{ $class->end_time->format('h:i A') }}</small>
                                </td>
                                <td>{{ $class->location }}</td>
                                <td>{{ $class->current_bookings }}/{{ $class->capacity }}</td>
                                <td>
                                    <a href="{{ route('classes.show', $class) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No upcoming classes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('classes.index') }}" class="btn btn-primary">View All Classes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<script>
    $(document).ready(function() {
        // Financial Chart (Income vs Expenses - Last 7 Days)
        var financialCtx = document.getElementById('financialChart').getContext('2d');
        var financialChart = new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: @json($financialChartLabels ?? []),
                datasets: [{
                    label: 'Income',
                    backgroundColor: "rgba(28, 200, 138, 0.5)",
                    borderColor: "rgba(28, 200, 138, 1)",
                    borderWidth: 2,
                    data: @json($financialChartIncome ?? []),
                }, {
                    label: 'Expenses',
                    backgroundColor: "rgba(231, 74, 59, 0.5)",
                    borderColor: "rgba(231, 74, 59, 1)",
                    borderWidth: 2,
                    data: @json($financialChartExpenses ?? []),
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                        }
                    }],
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': $' + tooltipItem.yLabel.toFixed(2);
                        }
                    }
                }
            }
        });

        // Monthly Trend Chart (Last 12 Months)
        var trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
        var trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($monthlyTrendLabels ?? []),
                datasets: [{
                    label: 'Income',
                    lineTension: 0.3,
                    backgroundColor: "rgba(28, 200, 138, 0.05)",
                    borderColor: "rgba(28, 200, 138, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointBorderColor: "rgba(28, 200, 138, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(28, 200, 138, 1)",
                    pointHoverBorderColor: "rgba(28, 200, 138, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: @json($monthlyTrendIncome ?? []),
                }, {
                    label: 'Expenses',
                    lineTension: 0.3,
                    backgroundColor: "rgba(231, 74, 59, 0.05)",
                    borderColor: "rgba(231, 74, 59, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(231, 74, 59, 1)",
                    pointBorderColor: "rgba(231, 74, 59, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(231, 74, 59, 1)",
                    pointHoverBorderColor: "rgba(231, 74, 59, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: @json($monthlyTrendExpenses ?? []),
                }],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                        }
                    }],
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': $' + tooltipItem.yLabel.toFixed(2);
                        }
                    }
                }
            }
        });

        // Set up attendance chart
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($attendanceChartLabels ?? []),
                datasets: [{
                    label: 'Attendance',
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: @json($attendanceChartData ?? []),
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                }
            }
        });
    });
</script>
@endpush 