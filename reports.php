<?php
include("db.php");
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: reports.php");
    exit();
}

// Set default time period (last 30 days if not specified)
$period = $_GET['period'] ?? '30days';
$custom_start = $_GET['start'] ?? '';
$custom_end = $_GET['end'] ?? '';   

// Calculate date ranges based on period
switch($period) {
    case '7days':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        $period_label = "Last 7 Days";
        break;
    case '30days':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        $period_label = "Last 30 Days";
        break;
    case '90days':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        $end_date = date('Y-m-d');
        $period_label = "Last 90 Days";
        break;
    case 'custom':
        $start_date = $custom_start;
        $end_date = $custom_end;
        $period_label = date('M d, Y', strtotime($start_date)) . " - " . date('M d, Y', strtotime($end_date));
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        $period_label = "Last 30 Days";
}

// Base query with date filter
$date_filter = "WHERE order_date BETWEEN '$start_date' AND '$end_date'";

// Fetch orders data for the period
$orders_query = "SELECT * FROM orders $date_filter ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Calculate key metrics
$total_orders = mysqli_num_rows($orders_result);
$total_revenue_query = "SELECT SUM(total_price) as total_revenue, 
                            COUNT(DISTINCT product_id) as unique_products,
                            COUNT(DISTINCT delivery_address) as unique_customers
                     FROM orders $date_filter";
$revenue_result = mysqli_query($conn, $total_revenue_query);
$metrics = mysqli_fetch_assoc($revenue_result);
$total_revenue = $metrics['total_revenue'];
$unique_products = $metrics['unique_products'];
$unique_customers = $metrics['unique_customers'];

// Daily revenue trend
$daily_trend_query = "SELECT 
    DATE(order_date) as date,
    COUNT(*) as order_count,
    SUM(total_price) as revenue,
    AVG(total_price) as avg_order_value
    FROM orders 
    $date_filter
    GROUP BY DATE(order_date)
    ORDER BY date";
$daily_trend_result = mysqli_query($conn, $daily_trend_query);

// Top selling products with detailed metrics
$top_products_query = "SELECT 
    product_name,
    COUNT(*) as order_count,
    SUM(quantity) as total_quantity,
    SUM(total_price) as total_revenue,
    AVG(total_price) as avg_price,
    MIN(total_price) as min_price,
    MAX(total_price) as max_price
    FROM orders 
    $date_filter
    GROUP BY product_name
    ORDER BY total_revenue DESC 
    LIMIT 10";
$top_products_result = mysqli_query($conn, $top_products_query);

// Customer demographics by city
$city_demographics_query = "SELECT 
    city,
    COUNT(*) as order_count,
    COUNT(DISTINCT delivery_address) as customer_count,
    SUM(total_price) as total_revenue,
    AVG(total_price) as avg_order_value
    FROM orders 
    $date_filter
    GROUP BY city
    ORDER BY total_revenue DESC";
$city_demographics_result = mysqli_query($conn, $city_demographics_query);

// Payment method analysis
$payment_analysis_query = "SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(total_price) as total_revenue,
    AVG(total_price) as avg_order_value,
    COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders $date_filter) as percentage
    FROM orders 
    $date_filter
    GROUP BY payment_method";
$payment_analysis_result = mysqli_query($conn, $payment_analysis_query);

// Order status distribution with revenue
$status_analysis_query = "SELECT 
    status,
    COUNT(*) as count,
    SUM(total_price) as total_revenue,
    AVG(total_price) as avg_order_value,
    COUNT(*) * 100.0 / (SELECT COUNT(*) FROM orders $date_filter) as percentage
    FROM orders 
    $date_filter
    GROUP BY status";
$status_analysis_result = mysqli_query($conn, $status_analysis_query);

// Calculate growth metrics
$previous_period_query = "SELECT 
    COUNT(*) as prev_orders,
    SUM(total_price) as prev_revenue
    FROM orders 
    WHERE order_date BETWEEN 
        DATE_SUB('$start_date', INTERVAL DATEDIFF('$end_date', '$start_date') DAY) 
        AND '$start_date'";
$previous_period_result = mysqli_query($conn, $previous_period_query);
$previous_metrics = mysqli_fetch_assoc($previous_period_result);

// Prevent division by zero errors
$prev_orders = $previous_metrics['prev_orders'] ?: 1; // Use 1 if prev_orders is 0
$prev_revenue = $previous_metrics['prev_revenue'] ?: 1; // Use 1 if prev_revenue is 0

$order_growth = $total_orders > 0 ? 
    (($total_orders - $previous_metrics['prev_orders']) / $prev_orders * 100) : 0;
$revenue_growth = $total_revenue > 0 ? 
    (($total_revenue - $previous_metrics['prev_revenue']) / $prev_revenue * 100) : 0;

// Get average daily revenue
$avg_daily_revenue_query = "SELECT AVG(daily_revenue) as avg_revenue FROM (
    SELECT DATE(order_date) as day, SUM(total_price) as daily_revenue
    FROM orders 
    $date_filter
    GROUP BY DATE(order_date)
) as daily_totals";
$avg_daily_result = mysqli_query($conn, $avg_daily_revenue_query);
$avg_daily_data = mysqli_fetch_assoc($avg_daily_result);
$avg_daily_revenue = $avg_daily_data['avg_revenue'];

// Get revenue by day of week
$dow_revenue_query = "SELECT 
    DAYNAME(order_date) as day_name,
    COUNT(*) as order_count,
    SUM(total_price) as total_revenue
    FROM orders 
    $date_filter
    GROUP BY DAYNAME(order_date)
    ORDER BY FIELD(DAYNAME(order_date), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$dow_revenue_result = mysqli_query($conn, $dow_revenue_query);

// Calculate revenue contribution percentages
$product_contribution_query = "SELECT 
    SUM(CASE WHEN product_rank <= 5 THEN product_revenue ELSE 0 END) / SUM(product_revenue) * 100 as top5_contribution
    FROM (
        SELECT 
            product_name,
            SUM(total_price) as product_revenue,
            ROW_NUMBER() OVER (ORDER BY SUM(total_price) DESC) as product_rank
        FROM orders 
        $date_filter
        GROUP BY product_name
    ) as ranked_products";
$product_contribution_result = mysqli_query($conn, $product_contribution_query);
$product_contribution = mysqli_fetch_assoc($product_contribution_result);
$top5_contribution = round($product_contribution['top5_contribution'], 1);

// Calculate city contribution percentages
$city_contribution_query = "SELECT 
    SUM(CASE WHEN city_rank <= 5 THEN city_orders ELSE 0 END) / SUM(city_orders) * 100 as top5_city_contribution
    FROM (
        SELECT 
            city,
            COUNT(*) as city_orders,
            ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) as city_rank
        FROM orders 
        $date_filter
        GROUP BY city
    ) as ranked_cities";
$city_contribution_result = mysqli_query($conn, $city_contribution_query);
$city_contribution = mysqli_fetch_assoc($city_contribution_result);
$top5_city_contribution = round($city_contribution['top5_city_contribution'], 1);

// Calculate payment method contribution percentages
$payment_contribution_query = "SELECT 
    SUM(CASE WHEN payment_method IN ('UPI', 'Credit Card') THEN payment_count ELSE 0 END) / SUM(payment_count) * 100 as top2_payment_contribution
    FROM (
        SELECT 
            payment_method,
            COUNT(*) as payment_count
        FROM orders 
        $date_filter
        GROUP BY payment_method
    ) as payment_counts";
$payment_contribution_result = mysqli_query($conn, $payment_contribution_query);
$payment_contribution = mysqli_fetch_assoc($payment_contribution_result);
$top2_payment_contribution = round($payment_contribution['top2_payment_contribution'], 1);

// Generate report date
$report_date = date('F d, Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Sales Analytics Report | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        :root {
            --primary-color: #1a3c6e;
            --secondary-color: #2c5282;
            --accent-color: #3182ce;
            --success-color: #38a169;
            --warning-color: #d69e2e;
            --danger-color: #e53e3e;
            --light-bg: #f7fafc;
            --dark-bg: #1a365d;
            --text-color: #2d3748;
            --light-text: #ffffff;
            --border-radius: 8px;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .dashboard-header {
            background: linear-gradient(120deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--light-text);
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border-bottom: 4px solid var(--accent-color);
        }

        .report-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .report-subtitle {
            font-weight: 400;
            opacity: 0.9;
        }

        .report-date {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-footer {
            background-color: rgba(0, 0, 0, 0.02);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.5rem;
            font-size: 0.9rem;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.25rem;
            font-size: 1.2rem;
            border-left: 4px solid var(--accent-color);
            padding-left: 10px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .metric-icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            opacity: 0.15;
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .metric-label {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metric-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--primary-color);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .table th {
            font-weight: 600;
            color: var(--primary-color);
            border-bottom-width: 1px;
            padding: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.01);
        }

        .growth-indicator {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .growth-positive {
            background-color: rgba(56, 161, 105, 0.1);
            color: var(--success-color);
        }

        .growth-negative {
            background-color: rgba(229, 62, 62, 0.1);
            color: var(--danger-color);
        }

        .date-filter {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
        }

        .date-filter .form-select,
        .date-filter .form-control {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0.5rem 1rem;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, rgba(0,0,0,0.05), rgba(0,0,0,0.1), rgba(0,0,0,0.05));
            margin: 2rem 0;
        }

        .recommendations-list {
            padding-left: 1rem;
        }

        .recommendations-list li {
            margin-bottom: 1rem;
            position: relative;
            padding-left: 1.5rem;
        }

        .recommendations-list li:before {
            content: "\f0eb";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            color: var(--accent-color);
        }

        .conclusion-box {
            background-color: rgba(49, 130, 206, 0.05);
            border-left: 4px solid var(--accent-color);
            padding: 1.5rem;
            border-radius: var(--border-radius);
        }

        .executive-summary {
            background-color: rgba(49, 130, 206, 0.05);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .print-btn {
            background-color: var(--dark-bg);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-speed) ease;
        }

        .print-btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        @media print {
            .no-print {
                display: none !important;
            }
            
            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .dashboard-header {
                box-shadow: none;
                border-bottom: 1px solid #ddd;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0.75rem;
            }
            
            .chart-container {
                height: 250px;
            }

            .card-body {
                padding: 1rem;
            }

            .metric-value {
                font-size: 1.5rem;
            }

            .table th,
            .table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header no-print">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 report-title">Professional Sales Analytics Report</h1>
                    <p class="mb-0 report-subtitle">Comprehensive Business Performance Analysis</p>
                    <p class="report-date mt-2">Generated on: <?php echo $report_date; ?></p>
                </div>
                <div>
                    <button class="print-btn me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <a href="adminDash.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Date Filter -->
        <div class="date-filter no-print">
            <form class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label for="period" class="form-label">Report Period</label>
                    <select class="form-select" name="period" id="period">
                        <option value="7days" <?php echo $period == '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30days" <?php echo $period == '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90days" <?php echo $period == '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                        <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-4 custom-date-range" style="display: <?php echo $period == 'custom' ? 'block' : 'none'; ?>">
                    <label for="daterange" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="daterange" name="daterange">
                    <input type="hidden" name="start" id="start" value="<?php echo $start_date; ?>">
                    <input type="hidden" name="end" id="end" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Executive Summary -->
        <div class="executive-summary">
            <h5 class="card-title">Executive Summary</h5>
            <p>This report presents a comprehensive analysis of sales performance for the period: <strong><?php echo $period_label; ?></strong>. The data indicates that the business has demonstrated <?php echo $order_growth >= 0 ? 'positive' : 'negative'; ?> growth trends in both order volume (<?php echo round($order_growth, 1); ?>%) and revenue (<?php echo round($revenue_growth, 1); ?>%). Key performance metrics show healthy customer engagement and product diversity.</p>
        </div>

        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart metric-icon"></i>
                        <h6 class="metric-label">Total Orders</h6>
                        <h2 class="metric-value"><?php echo number_format($total_orders); ?></h2>
                        <span class="growth-indicator <?php echo $order_growth >= 0 ? 'growth-positive' : 'growth-negative'; ?>">
                            <i class="fas fa-<?php echo $order_growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs(round($order_growth, 1)); ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <i class="fas fa-rupee-sign metric-icon"></i>
                        <h6 class="metric-label">Total Revenue</h6>
                        <h2 class="metric-value">₹<?php echo number_format($total_revenue, 2); ?></h2>
                        <span class="growth-indicator <?php echo $revenue_growth >= 0 ? 'growth-positive' : 'growth-negative'; ?>">
                            <i class="fas fa-<?php echo $revenue_growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs(round($revenue_growth, 1)); ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <i class="fas fa-rupee-sign metric-icon"></i>
                        <h6 class="metric-label">Total Revenue</h6>
                        <h2 class="metric-value">₹<?php echo number_format($total_revenue, 2); ?></h2>
                        <span class="growth-indicator <?php echo $revenue_growth >= 0 ? 'growth-positive' : 'growth-negative'; ?>">
                            <i class="fas fa-<?php echo $revenue_growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs(round($revenue_growth, 1)); ?>%
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <i class="fas fa-users metric-icon"></i>
                        <h6 class="metric-label">Unique Customers</h6>
                        <h2 class="metric-value"><?php echo number_format($unique_customers); ?></h2>
                        <p class="mb-0 text-muted small">Based on unique delivery addresses</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <i class="fas fa-box metric-icon"></i>
                        <h6 class="metric-label">Unique Products</h6>
                        <h2 class="metric-value"><?php echo number_format($unique_products); ?></h2>
                        <p class="mb-0 text-muted small">Average Revenue: ₹<?php echo number_format($total_revenue / $unique_products, 2); ?> per product</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Revenue Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i> Daily Revenue Trend
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dailyRevenueChart"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="d-inline-block" style="width: 12px; height: 12px; background-color: rgba(49, 130, 206, 0.5); border-radius: 50%;"></span>
                            </div>
                            <div>
                                <h6 class="mb-0 small">Avg. Daily Revenue</h6>
                                <p class="mb-0 fw-bold">₹<?php echo number_format($avg_daily_revenue, 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="d-inline-block" style="width: 12px; height: 12px; background-color: rgba(56, 161, 105, 0.5); border-radius: 50%;"></span>
                            </div>
                            <div>
                                <h6 class="mb-0 small">Highest Day</h6>
                                <p class="mb-0 fw-bold">₹<?php 
                                    $max_revenue = 0;
                                    $max_day = '';
                                    mysqli_data_seek($daily_trend_result, 0);
                                    while($day_data = mysqli_fetch_assoc($daily_trend_result)) {
                                        if($day_data['revenue'] > $max_revenue) {
                                            $max_revenue = $day_data['revenue'];
                                            $max_day = date('M d', strtotime($day_data['date']));
                                        }
                                    }
                                    echo number_format($max_revenue, 2) . ' (' . $max_day . ')';
                                ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="d-inline-block" style="width: 12px; height: 12px; background-color: rgba(229, 62, 62, 0.5); border-radius: 50%;"></span>
                            </div>
                            <div>
                                <h6 class="mb-0 small">Growth Trend</h6>
                                <p class="mb-0 fw-bold"><?php 
                                    // Resetting the result set pointer
                                    mysqli_data_seek($daily_trend_result, 0);
                                    $trend_data = [];
                                    while($day_data = mysqli_fetch_assoc($daily_trend_result)) {
                                        $trend_data[] = $day_data['revenue'];
                                    }
                                    
                                    // Calculate simple trend (comparing first half to second half)
                                    $mid_point = floor(count($trend_data) / 2);
                                    $first_half = array_slice($trend_data, 0, $mid_point);
                                    $second_half = array_slice($trend_data, $mid_point);
                                    
                                    $first_half_avg = count($first_half) > 0 ? array_sum($first_half) / count($first_half) : 0;
                                    $second_half_avg = count($second_half) > 0 ? array_sum($second_half) / count($second_half) : 0;
                                    
                                    $trend_pct = $first_half_avg > 0 ? (($second_half_avg - $first_half_avg) / $first_half_avg * 100) : 0;
                                    
                                    echo ($trend_pct >= 0 ? '+' : '') . round($trend_pct, 1) . '% ';
                                    echo $trend_pct >= 0 ? 'Upward' : 'Downward';
                                ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products and Customer Demographics -->
        <div class="row">
            <!-- Top Products -->
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-trophy me-2"></i> Top Selling Products
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Orders</th>
                                        <th>Quantity</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $product_count = 0;
                                    while ($product = mysqli_fetch_assoc($top_products_result)) {
                                        $product_count++;
                                        echo '<tr>';
                                        echo '<td>' . $product['product_name'] . '</td>';
                                        echo '<td>' . number_format($product['order_count']) . '</td>';
                                        echo '<td>' . number_format($product['total_quantity']) . '</td>';
                                        echo '<td>₹' . number_format($product['total_revenue'], 2) . '</td>';
                                        echo '</tr>';
                                        
                                        // Limit to 5 rows
                                        if ($product_count >= 5) break;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Top 5 products contribute <strong><?php echo $top5_contribution; ?>%</strong> of total revenue</small>
                    </div>
                </div>
            </div>
            
            <!-- Customer Demographics by City -->
            <div class="col-md-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt me-2"></i> Customer Demographics by City
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="cityDemographicsChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Top 5 cities contribute <strong><?php echo $top5_city_contribution; ?>%</strong> of total orders</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods and Order Status -->
        <div class="row">
            <!-- Payment Method Analysis -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-credit-card me-2"></i> Payment Method Analysis
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="paymentMethodChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>UPI and Credit Card payments account for <strong><?php echo $top2_payment_contribution; ?>%</strong> of all transactions</small>
                    </div>
                </div>
            </div>
            
            <!-- Order Status Distribution -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tasks me-2"></i> Order Status Distribution
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Orders</th>
                                        <th>Revenue</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    while ($status = mysqli_fetch_assoc($status_analysis_result)) {
                                        $status_class = '';
                                        switch(strtolower($status['status'])) {
                                            case 'completed':
                                                $status_class = 'bg-success text-white';
                                                break;
                                            case 'processing':
                                                $status_class = 'bg-primary text-white';
                                                break;
                                            case 'pending':
                                                $status_class = 'bg-warning text-dark';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'bg-danger text-white';
                                                break;
                                            case 'refunded':
                                                $status_class = 'bg-secondary text-white';
                                                break;
                                        }
                                        echo '<tr>';
                                        echo '<td><span class="status-badge ' . $status_class . '">' . $status['status'] . '</span></td>';
                                        echo '<td>' . number_format($status['count']) . '</td>';
                                        echo '<td>₹' . number_format($status['total_revenue'], 2) . '</td>';
                                        echo '<td>' . round($status['percentage'], 1) . '%</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day of Week Analysis -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-week me-2"></i> Revenue by Day of Week
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="dayOfWeekChart"></canvas>
                </div>
            </div>
            <div class="card-footer text-muted">
                <?php 
                // Find best performing day
                mysqli_data_seek($dow_revenue_result, 0);
                $best_day = '';
                $best_day_revenue = 0;
                while ($day_data = mysqli_fetch_assoc($dow_revenue_result)) {
                    if ($day_data['total_revenue'] > $best_day_revenue) {
                        $best_day_revenue = $day_data['total_revenue'];
                        $best_day = $day_data['day_name'];
                    }
                }
                ?>
                <small>Best performing day: <strong><?php echo $best_day; ?></strong> with average revenue of <strong>₹<?php echo number_format($best_day_revenue, 2); ?></strong></small>
            </div>
        </div>

        <!-- Business Insights and Recommendations -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-lightbulb me-2"></i> Business Insights and Recommendations
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title">Key Observations</h5>
                        <ul class="recommendations-list">
                            <li><strong>Revenue Trend:</strong> <?php echo $revenue_growth >= 0 ? 'Positive growth trend of ' . round($revenue_growth, 1) . '%' : 'Decline of ' . abs(round($revenue_growth, 1)) . '%'; ?> compared to previous period.</li>
                            <li><strong>Product Performance:</strong> Top 5 products constitute <?php echo $top5_contribution; ?>% of total revenue, indicating <?php echo $top5_contribution > 70 ? 'high dependency on few products' : 'balanced product portfolio'; ?>.</li>
                            <li><strong>Regional Performance:</strong> Top 5 cities contribute <?php echo $top5_city_contribution; ?>% of orders, showing <?php echo $top5_city_contribution > 70 ? 'geographical concentration' : 'good geographical diversity'; ?>.</li>
                            <li><strong>Payment Trends:</strong> <?php echo $top2_payment_contribution; ?>% of transactions use digital methods (UPI and Credit Card).</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title">Strategic Recommendations</h5>
                        <ul class="recommendations-list">
                            <?php if ($revenue_growth < 0): ?>
                            <li><strong>Growth Strategy:</strong> Implement targeted marketing campaigns to reverse the negative revenue trend of <?php echo abs(round($revenue_growth, 1)); ?>%.</li>
                            <?php else: ?>
                            <li><strong>Growth Strategy:</strong> Leverage the positive momentum with expansion of product lines and market reach.</li>
                            <?php endif; ?>
                            
                            <?php if ($top5_contribution > 70): ?>
                            <li><strong>Product Diversification:</strong> Reduce dependency on top products by promoting and enhancing other product lines.</li>
                            <?php else: ?>
                            <li><strong>Product Optimization:</strong> Continue balanced approach while identifying potential star products.</li>
                            <?php endif; ?>
                            
                            <?php if ($top5_city_contribution > 70): ?>
                            <li><strong>Market Expansion:</strong> Explore new geographical regions to reduce concentration risk.</li>
                            <?php else: ?>
                            <li><strong>Regional Focus:</strong> Maintain geographical diversity while optimizing high-potential regions.</li>
                            <?php endif; ?>
                            
                            <li><strong>Payment Strategy:</strong> Continue enhancing digital payment options while ensuring all customer segments are served.</li>
                        </ul>
                    </div>
                </div>
                
                <div class="section-divider"></div>
                
                <div class="conclusion-box">
                    <h5 class="mb-3">Executive Conclusion</h5>
                    <p><?php 
                        if ($revenue_growth >= 5 && $order_growth >= 5) {
                            echo "The business is showing strong growth across key metrics. Focus on sustaining momentum through strategic expansion and optimization of product offerings.";
                        } elseif ($revenue_growth >= 0 && $order_growth >= 0) {
                            echo "The business is demonstrating stable performance with moderate growth. Opportunities exist to accelerate growth through targeted initiatives in product development and market expansion.";
                        } else {
                            echo "The business is facing challenges with declining metrics. Immediate attention required to address underlying issues, reevaluate product strategy, and implement targeted marketing campaigns to reverse the trend.";
                        }
                    ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        // Initialize date range picker
        $(function() {
            $('#daterange').daterangepicker({
                startDate: moment('<?php echo $start_date; ?>'),
                endDate: moment('<?php echo $end_date; ?>'),
                maxDate: moment(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            }, function(start, end) {
                $('#start').val(start.format('YYYY-MM-DD'));
                $('#end').val(end.format('YYYY-MM-DD'));
            });

            // Show/hide custom date range based on select value
            $('#period').change(function() {
                if ($(this).val() === 'custom') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            });
        });

        // Chart.js Configuration
        document.addEventListener('DOMContentLoaded', function() {
            // Chart.js Global Configuration
            Chart.defaults.font.family = "'Segoe UI', 'Helvetica Neue', 'Arial', sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#2d3748';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(26, 32, 44, 0.8)';
            Chart.defaults.plugins.tooltip.padding = 10;
            Chart.defaults.plugins.tooltip.cornerRadius = 4;
            Chart.defaults.plugins.tooltip.titleFont = { size: 14, weight: 'bold' };
            Chart.defaults.plugins.legend.labels.usePointStyle = true;
            Chart.defaults.plugins.legend.labels.padding = 15;

            // Daily Revenue Chart
            <?php
            mysqli_data_seek($daily_trend_result, 0);
            $dates = [];
            $revenues = [];
            $order_counts = [];

            while ($day_data = mysqli_fetch_assoc($daily_trend_result)) {
                $dates[] = date('M d', strtotime($day_data['date']));
                $revenues[] = $day_data['revenue'];
                $order_counts[] = $day_data['order_count'];
            }
            ?>

            var dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
            var dailyRevenueChart = new Chart(dailyRevenueCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: <?php echo json_encode($revenues); ?>,
                            backgroundColor: 'rgba(49, 130, 206, 0.1)',
                            borderColor: 'rgba(49, 130, 206, 0.8)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(49, 130, 206, 1)',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Orders',
                            data: <?php echo json_encode($order_counts); ?>,
                            backgroundColor: 'rgba(56, 161, 105, 0.0)',
                            borderColor: 'rgba(56, 161, 105, 0.8)',
                            borderWidth: 2,
                            pointBackgroundColor: 'rgba(56, 161, 105, 1)',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Revenue (₹)'
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Order Count'
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.label === 'Revenue (₹)') {
                                        label += '₹' + new Intl.NumberFormat().format(context.raw);
                                    } else {
                                        label += new Intl.NumberFormat().format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // City Demographics Chart
            <?php
            mysqli_data_seek($city_demographics_result, 0);
            $cities = [];
            $city_revenues = [];
            $city_orders = [];
            $city_count = 0;

            while ($city_data = mysqli_fetch_assoc($city_demographics_result)) {
                $cities[] = $city_data['city'];
                $city_revenues[] = $city_data['total_revenue'];
                $city_orders[] = $city_data['order_count'];
                $city_count++;
                
                if ($city_count >= 5) break;
            }
            ?>

            var cityDemographicsCtx = document.getElementById('cityDemographicsChart').getContext('2d');
            var cityDemographicsChart = new Chart(cityDemographicsCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($cities); ?>,
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: <?php echo json_encode($city_revenues); ?>,
                            backgroundColor: 'rgba(49, 130, 206, 0.7)',
                            borderColor: 'rgba(49, 130, 206, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Revenue (₹)'
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₹' + new Intl.NumberFormat().format(context.raw);
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Payment Method Chart
            <?php
            mysqli_data_seek($payment_analysis_result, 0);
            $payment_methods = [];
            $payment_counts = [];
            $payment_colors = [
                'rgba(49, 130, 206, 0.7)',   // Blue
                'rgba(56, 161, 105, 0.7)',   // Green
                'rgba(214, 158, 46, 0.7)',   // Yellow
                'rgba(229, 62, 62, 0.7)',    // Red
                'rgba(159, 122, 234, 0.7)',  // Purple
            ];

            while ($payment_data = mysqli_fetch_assoc($payment_analysis_result)) {
                $payment_methods[] = $payment_data['payment_method'];
                $payment_counts[] = $payment_data['count'];
            }
            ?>

            var paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
            var paymentMethodChart = new Chart(paymentMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($payment_methods); ?>,
                    datasets: [
                        {
                            data: <?php echo json_encode($payment_counts); ?>,
                            backgroundColor: <?php echo json_encode(array_slice($payment_colors, 0, count($payment_methods))); ?>,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value + ' orders (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // Day of Week Chart
            <?php
            mysqli_data_seek($dow_revenue_result, 0);
            $days = [];
            $day_revenues = [];
            $day_orders = [];

            while ($day_data = mysqli_fetch_assoc($dow_revenue_result)) {
                $days[] = $day_data['day_name'];
                $day_revenues[] = $day_data['total_revenue'];
                $day_orders[] = $day_data['order_count'];
            }
            ?>

            var dayOfWeekCtx = document.getElementById('dayOfWeekChart').getContext('2d');
            var dayOfWeekChart = new Chart(dayOfWeekCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($days); ?>,
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: <?php echo json_encode($day_revenues); ?>,
                            backgroundColor: 'rgba(49, 130, 206, 0.7)',
                            borderColor: 'rgba(49, 130, 206, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Orders',
                            data: <?php echo json_encode($day_orders); ?>,
                            backgroundColor: 'rgba(56, 161, 105, 0.7)',
                            borderColor: 'rgba(56, 161, 105, 1)',
                            borderWidth: 1,
                            type: 'line',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Revenue (₹)'
                            },
                            grid: {
                                drawBorder: false,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Order Count'
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.label === 'Revenue (₹)') {
                                        label += '₹' + new Intl.NumberFormat().format(context.raw);
                                    } else {
                                        label += new Intl.NumberFormat().format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>