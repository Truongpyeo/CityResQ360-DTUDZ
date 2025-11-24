"use client";
import React from "react";
import { ApexOptions } from "apexcharts";
import dynamic from "next/dynamic";

const ReactApexChart = dynamic(() => import("react-apexcharts"), {
  ssr: false,
});

interface StatsBarChartProps {
  data: Array<{
    name: string;
    aqi: number;
    temperature: number;
    energy: number;
    schools: number;
    education: number;
  }>;
}

export default function StatsBarChart({ data }: StatsBarChartProps) {
  const options: ApexOptions = {
    colors: ["#22c55e", "#f59e0b", "#3b82f6", "#a855f7", "#ec4899"],
    chart: {
      fontFamily: "Outfit, sans-serif",
      type: "bar",
      height: 400,
      toolbar: {
        show: false,
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "55%",
        borderRadius: 6,
        borderRadiusApplication: "end",
        dataLabels: {
          position: "top",
        },
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      show: true,
      width: 2,
      colors: ["transparent"],
    },
    xaxis: {
      categories: data.map((item) => item.name),
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
    yaxis: {
      labels: {
        style: {
          fontSize: "12px",
          colors: "#6B7280",
        },
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
    fill: {
      opacity: 1,
    },
    tooltip: {
      y: {
        formatter: (val: number) => `${val}`,
      },
    },
  };

  const series = [
    {
      name: "AQI",
      data: data.map((item) => item.aqi),
    },
    {
      name: "Nhiệt độ (°C)",
      data: data.map((item) => item.temperature),
    },
    {
      name: "Năng lượng xanh (%)",
      data: data.map((item) => item.energy),
    },
    {
      name: "Trường học",
      data: data.map((item) => item.schools),
    },
    {
      name: "Chỉ số giáo dục",
      data: data.map((item) => item.education),
    },
  ];

  return (
    <div className="w-full">
      <ReactApexChart options={options} series={series} type="bar" height={400} />
    </div>
  );
}

