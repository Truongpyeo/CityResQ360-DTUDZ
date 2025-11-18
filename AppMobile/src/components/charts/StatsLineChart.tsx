"use client";
import React from "react";
import { ApexOptions } from "apexcharts";
import dynamic from "next/dynamic";

const ReactApexChart = dynamic(() => import("react-apexcharts"), {
  ssr: false,
});

interface StatsLineChartProps {
  data: Array<{
    month: string;
    aqi: number;
    energy: number;
    education: number;
  }>;
}

export default function StatsLineChart({ data }: StatsLineChartProps) {
  const options: ApexOptions = {
    colors: ["#22c55e", "#3b82f6", "#a855f7"],
    chart: {
      fontFamily: "Outfit, sans-serif",
      height: 350,
      type: "area",
      toolbar: {
        show: false,
      },
    },
    stroke: {
      curve: "smooth",
      width: [3, 3, 3],
    },
    fill: {
      type: "gradient",
      gradient: {
        opacityFrom: 0.4,
        opacityTo: 0.1,
        stops: [0, 100],
      },
    },
    markers: {
      size: [5, 5, 5],
      strokeColors: "#fff",
      strokeWidth: 2,
      hover: {
        size: 7,
      },
    },
    grid: {
      borderColor: "#E5E7EB",
      strokeDashArray: 3,
      xaxis: {
        lines: {
          show: false,
        },
      },
      yaxis: {
        lines: {
          show: true,
        },
      },
    },
    dataLabels: {
      enabled: false,
    },
    tooltip: {
      enabled: true,
      shared: true,
      intersect: false,
    },
    xaxis: {
      categories: data.map((item) => item.month),
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
      labels: {
        style: {
          fontSize: "12px",
          colors: "#6B7280",
        },
      },
    },
    yaxis: {
      labels: {
        style: {
          fontSize: "12px",
          colors: "#6B7280",
        },
      },
    },
    legend: {
      show: true,
      position: "top",
      horizontalAlign: "right",
      fontFamily: "Outfit",
      fontSize: "12px",
      markers: {
        width: 12,
        height: 12,
        radius: 6,
      },
    },
  };

  const series = [
    {
      name: "AQI",
      data: data.map((item) => item.aqi),
    },
    {
      name: "Năng lượng xanh (%)",
      data: data.map((item) => item.energy),
    },
    {
      name: "Giáo dục",
      data: data.map((item) => item.education),
    },
  ];

  return (
    <div className="w-full">
      <ReactApexChart options={options} series={series} type="area" height={350} />
    </div>
  );
}

