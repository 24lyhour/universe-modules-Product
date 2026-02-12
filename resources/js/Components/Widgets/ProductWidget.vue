<script setup lang="ts">
import { computed, watch } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
} from '@/components/ui/chart';
import { Donut } from '@unovis/ts';
import {
    VisXYContainer,
    VisGroupedBar,
    VisAxis,
    VisSingleContainer,
    VisDonut,
    VisArea,
    VisLine,
} from '@unovis/vue';
import { ref } from 'vue';
import {
    Package,
    ShoppingCart,
    TrendingUp,
    AlertTriangle,
    RefreshCw,
    Calendar,
    ArrowUpRight,
    ArrowDownRight,
    Activity,
    DollarSign,
    BarChart3,
    Zap,
} from 'lucide-vue-next';
import type { ChartConfig } from '@/components/ui/chart';

// Types
export interface ProductMetrics {
    total: number;
    active: number;
    outOfStock: number;
    lowStock: number;
    discontinued: number;
    totalRevenue: number;
    averagePrice: number;
    totalSold: number;
    growthPercent: number;
    previousPeriodTotal: number;
    topPerformingCount: number;
    inventoryValue: number;
}

export interface SalesDataPoint {
    label: string;
    value: number;
    revenue: number;
    sold: number;
}

export interface CategoryDistribution {
    category: string;
    count: number;
    color: string;
}

export interface ProductWidgetProps {
    metrics: ProductMetrics;
    salesData: SalesDataPoint[];
    categoryDistribution: CategoryDistribution[];
    dateRange?: string;
    loading?: boolean;
    showStats?: boolean;
    showSales?: boolean;
    showCategory?: boolean;
}

const props = withDefaults(defineProps<ProductWidgetProps>(), {
    dateRange: '30d',
    loading: false,
    showStats: true,
    showSales: true,
    showCategory: true,
});

const emit = defineEmits<{
    (e: 'dateRangeChange', value: string): void;
    (e: 'refresh'): void;
}>();

const selectedDateRange = ref(props.dateRange);

// Date range options
const dateRangeOptions = [
    { value: 'today', label: 'Today' },
    { value: '7d', label: 'Last 7 Days' },
    { value: '30d', label: 'Last 30 Days' },
    { value: '90d', label: 'Last 90 Days' },
    { value: 'year', label: 'This Year' },
];

// Computed metrics
const growthTrend = computed(() => ({
    isPositive: props.metrics.growthPercent >= 0,
    value: Math.abs(props.metrics.growthPercent),
}));

const stockStatus = computed(() => ({
    isHealthy: props.metrics.lowStock < (props.metrics.total * 0.1),
    ratio: props.metrics.lowStock / props.metrics.total,
}));

// Chart configs
const salesChartConfig: ChartConfig = {
    value: { label: 'Products Sold', color: 'var(--chart-1)' },
    revenue: { label: 'Revenue', color: 'var(--chart-2)' },
};

const categoryChartConfig: ChartConfig = {
    active: { label: 'Active', color: 'var(--chart-1)' },
    lowStock: { label: 'Low Stock', color: 'var(--chart-2)' },
    outOfStock: { label: 'Out of Stock', color: 'var(--chart-3)' },
    discontinued: { label: 'Discontinued', color: 'var(--chart-4)' },
};

// Donut chart data
const donutData = computed(() => [
    { status: 'active', label: 'Active', value: props.metrics.active, fill: 'var(--color-active)' },
    { status: 'lowStock', label: 'Low Stock', value: props.metrics.lowStock, fill: 'var(--color-lowStock)' },
    { status: 'outOfStock', label: 'Out of Stock', value: props.metrics.outOfStock, fill: 'var(--color-outOfStock)' },
    { status: 'discontinued', label: 'Discontinued', value: props.metrics.discontinued, fill: 'var(--color-discontinued)' },
]);

// Status bar data
const statusBarData = computed(() => [
    { label: 'Active', value: props.metrics.active },
    { label: 'Low Stock', value: props.metrics.lowStock },
    { label: 'Out of Stock', value: props.metrics.outOfStock },
    { label: 'Discontinued', value: props.metrics.discontinued },
]);

// Watch date range changes
watch(selectedDateRange, (newValue) => {
    emit('dateRangeChange', newValue);
});

// Methods
const handleRefresh = () => {
    emit('refresh');
};

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};

const formatCurrency = (num: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
};

const formatPercent = (num: number) => {
    return `${num.toFixed(1)}%`;
};

// Get status badge variant
const getStatusBadgeVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status.toLowerCase()) {
        case 'active':
            return 'default';
        case 'low stock':
        case 'lowstock':
            return 'secondary';
        case 'out of stock':
        case 'outofstock':
            return 'destructive';
        case 'discontinued':
            return 'outline';
        default:
            return 'secondary';
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header with Date Filter -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Product Performance Metrics</h2>
                <p class="text-sm text-muted-foreground">Track sales and inventory overview</p>
            </div>
            <div class="flex items-center gap-2">
                <Select v-model="selectedDateRange">
                    <SelectTrigger class="w-[160px]">
                        <Calendar class="mr-2 h-4 w-4" />
                        <SelectValue placeholder="Select period" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in dateRangeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button variant="outline" size="icon" @click="handleRefresh" :disabled="loading">
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                </Button>
            </div>
        </div>

        <!-- Key Metrics Grid -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Products -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Products</CardTitle>
                    <Package class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatNumber(metrics.total) }}</div>
                    <div class="flex items-center text-xs">
                        <component
                            :is="growthTrend.isPositive ? ArrowUpRight : ArrowDownRight"
                            class="mr-1 h-3 w-3"
                            :class="growthTrend.isPositive ? 'text-green-500' : 'text-red-500'"
                        />
                        <span :class="growthTrend.isPositive ? 'text-green-500' : 'text-red-500'">
                            {{ growthTrend.isPositive ? '+' : '-' }}{{ formatPercent(growthTrend.value) }}
                        </span>
                        <span class="ml-1 text-muted-foreground">vs previous period</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Active Products -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Products</CardTitle>
                    <TrendingUp class="h-4 w-4 text-green-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ formatNumber(metrics.active) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.active / metrics.total) * 100) }} of total
                    </p>
                </CardContent>
            </Card>

            <!-- Total Revenue -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                    <DollarSign class="h-4 w-4 text-blue-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(metrics.totalRevenue) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatCurrency(metrics.averagePrice) }} average price
                    </p>
                </CardContent>
            </Card>

            <!-- Total Sold -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Sold</CardTitle>
                    <ShoppingCart class="h-4 w-4 text-purple-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-purple-600">{{ formatNumber(metrics.totalSold) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatNumber(Math.floor(metrics.totalSold / Math.max(metrics.total, 1))) }} per product avg
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Secondary Metrics Row -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Low Stock Items -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Low Stock Items</CardTitle>
                    <Zap class="h-4 w-4 text-amber-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold" :class="stockStatus.isHealthy ? 'text-green-600' : 'text-amber-600'">
                        {{ formatNumber(metrics.lowStock) }}
                    </div>
                    <div class="mt-1 h-1.5 w-full rounded-full bg-muted">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="stockStatus.isHealthy ? 'bg-green-500' : 'bg-amber-500'"
                            :style="{ width: `${Math.min(stockStatus.ratio * 100, 100)}%` }"
                        ></div>
                    </div>
                </CardContent>
            </Card>

            <!-- Out of Stock -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Out of Stock</CardTitle>
                    <AlertTriangle class="h-4 w-4 text-red-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold" :class="metrics.outOfStock > 0 ? 'text-red-600' : 'text-green-600'">
                        {{ formatNumber(metrics.outOfStock) }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.outOfStock / metrics.total) * 100) }} of inventory
                    </p>
                </CardContent>
            </Card>

            <!-- Inventory Value -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Inventory Value</CardTitle>
                    <BarChart3 class="h-4 w-4 text-emerald-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ formatCurrency(metrics.inventoryValue) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatCurrency(metrics.inventoryValue / Math.max(metrics.active, 1)) }} per active product
                    </p>
                </CardContent>
            </Card>

            <!-- Top Performing -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Top Performing</CardTitle>
                    <TrendingUp class="h-4 w-4 text-yellow-500" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-yellow-600">{{ formatNumber(metrics.topPerformingCount) }}</div>
                    <p class="text-xs text-muted-foreground">
                        {{ formatPercent((metrics.topPerformingCount / metrics.active) * 100) }} of active products
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Out of Stock Alert -->
        <Card v-if="showStats && metrics.outOfStock > 0" class="border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/20">
            <CardContent class="flex items-center gap-4 pt-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50">
                    <AlertTriangle class="h-5 w-5 text-red-600" />
                </div>
                <div class="flex-1">
                    <p class="font-medium text-red-800 dark:text-red-200">
                        {{ formatNumber(metrics.outOfStock) }} products out of stock
                    </p>
                    <p class="text-sm text-red-600 dark:text-red-400">
                        Restock immediately to avoid lost sales
                    </p>
                </div>
                <Button variant="outline" size="sm" class="border-red-300 text-red-700 hover:bg-red-100">
                    View Out of Stock
                </Button>
            </CardContent>
        </Card>

        <!-- Charts Section -->
        <div v-if="showSales || showCategory" class="grid gap-6 lg:grid-cols-2">
            <!-- Sales Trend Chart -->
            <Card v-if="showSales">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Activity class="h-5 w-5" />
                        Sales Performance Trend
                    </CardTitle>
                    <CardDescription>Product sales and revenue over time</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="salesChartConfig" class="h-[280px]" cursor>
                        <VisXYContainer :data="props.salesData" :margin="{ top: 10, bottom: 10 }">
                            <VisArea
                                :x="(_: SalesDataPoint, i: number) => i"
                                :y="(d: SalesDataPoint) => d.value"
                                :color="salesChartConfig.value.color"
                                :opacity="0.4"
                            />
                            <VisLine
                                :x="(_: SalesDataPoint, i: number) => i"
                                :y="(d: SalesDataPoint) => d.value"
                                :color="salesChartConfig.value.color"
                                :line-width="2"
                            />
                            <VisAxis
                                type="x"
                                :tick-line="false"
                                :domain-line="false"
                                :grid-line="false"
                                :tick-format="(i: number) => props.salesData[i]?.label || ''"
                            />
                            <VisAxis
                                type="y"
                                :num-ticks="5"
                                :tick-line="false"
                                :domain-line="false"
                            />
                            <ChartTooltip />
                            <ChartCrosshair
                                :template="(d: SalesDataPoint) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='font-medium'>${d.label}</div><div class='text-muted-foreground'>${d.sold.toLocaleString()} sold</div><div class='text-muted-foreground'>${(d.revenue / 1000).toFixed(1)}K revenue</div></div>`"
                                :color="salesChartConfig.value.color"
                            />
                        </VisXYContainer>
                    </ChartContainer>
                </CardContent>
            </Card>

            <!-- Category Distribution Chart -->
            <Card v-if="showCategory">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Package class="h-5 w-5" />
                        Product Status Distribution
                    </CardTitle>
                    <CardDescription>Breakdown by product status</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Donut Chart -->
                        <ChartContainer
                            :config="categoryChartConfig"
                            class="h-[200px]"
                            :style="{
                                '--vis-donut-central-label-font-size': 'var(--text-2xl)',
                                '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
                                '--vis-donut-central-label-text-color': 'var(--foreground)',
                                '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                            }"
                        >
                            <VisSingleContainer :data="donutData" :margin="{ top: 10, bottom: 10 }">
                                <VisDonut
                                    :value="(d: any) => d.value"
                                    :color="(d: any) => categoryChartConfig[d.status as keyof typeof categoryChartConfig]?.color"
                                    :arc-width="40"
                                    :pad-angle="0.02"
                                    :corner-radius="4"
                                    :central-label="props.metrics.total.toLocaleString()"
                                    central-sub-label="Products"
                                />
                                <ChartTooltip
                                    :triggers="{
                                        [Donut.selectors.segment]: (d: any) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='flex items-center gap-2'><span class='h-2 w-2 rounded-full' style='background-color: ${categoryChartConfig[d.status as keyof typeof categoryChartConfig]?.color}'></span><span class='font-medium'>${d.label}</span></div><div class='text-muted-foreground'>${d.value.toLocaleString()} products</div></div>`,
                                    }"
                                />
                            </VisSingleContainer>
                        </ChartContainer>

                        <!-- Legend -->
                        <div class="flex flex-col justify-center space-y-3">
                            <div
                                v-for="(item, index) in donutData"
                                :key="item.label"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :class="{
                                            'bg-chart-1': index === 0,
                                            'bg-chart-2': index === 1,
                                            'bg-chart-3': index === 2,
                                            'bg-chart-4': index === 3,
                                        }"
                                    ></span>
                                    <span class="text-sm">{{ item.label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ formatNumber(item.value) }}</span>
                                    <Badge :variant="getStatusBadgeVariant(item.label)" class="text-xs">
                                        {{ formatPercent((item.value / metrics.total) * 100) }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Status Bar Chart -->
        <Card v-if="showCategory">
            <CardHeader>
                <CardTitle>Product Status Overview</CardTitle>
                <CardDescription>All product statuses at a glance</CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer :config="categoryChartConfig" class="h-[200px]" cursor>
                    <VisXYContainer :data="statusBarData" :margin="{ left: -24 }" :y-domain="[0, undefined]">
                        <VisGroupedBar
                            :x="(_: any, i: number) => i"
                            :y="(d: any) => d.value"
                            :color="(_: any, i: number) => {
                                const colors = [
                                    categoryChartConfig.active.color,
                                    categoryChartConfig.lowStock.color,
                                    categoryChartConfig.outOfStock.color,
                                    categoryChartConfig.discontinued.color,
                                ];
                                return colors[i % colors.length];
                            }"
                            :bar-padding="0.1"
                            :rounded-corners="4"
                        />
                        <VisAxis
                            type="x"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :tick-format="(i: number) => statusBarData[i]?.label || ''"
                        />
                        <VisAxis
                            type="y"
                            :num-ticks="3"
                            :tick-line="false"
                            :domain-line="false"
                        />
                        <ChartTooltip />
                        <ChartCrosshair
                            :template="(d: any) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='font-medium'>${d.label}</div><div class='text-muted-foreground'>${d.value.toLocaleString()} products</div></div>`"
                            color="#0000"
                        />
                    </VisXYContainer>
                </ChartContainer>
            </CardContent>
        </Card>
    </div>
</template>
