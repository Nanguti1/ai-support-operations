'use client';

import type { TicketStats } from '@/lib/types';

interface TicketStatsProps {
  stats: TicketStats;
  isLoading?: boolean;
}

export function TicketStats({ stats, isLoading }: TicketStatsProps) {
  const statCards = [
    {
      label: 'Open tickets',
      value: stats.open,
      color: 'text-emerald-600',
      bgColor: 'bg-emerald-50',
      borderColor: 'border-emerald-200',
    },
    {
      label: 'In progress',
      value: stats.in_progress,
      color: 'text-blue-600',
      bgColor: 'bg-blue-50',
      borderColor: 'border-blue-200',
    },
    {
      label: 'Closed',
      value: stats.closed,
      color: 'text-gray-600',
      bgColor: 'bg-gray-50',
      borderColor: 'border-gray-200',
    },
  ];

  if (isLoading) {
    return (
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        {[1, 2, 3].map((i) => (
          <div
            key={i}
            className="bg-white border border-gray-200 rounded-lg p-6 animate-pulse"
          >
            <div className="h-4 bg-gray-200 rounded w-24 mb-2"></div>
            <div className="h-8 bg-gray-200 rounded w-12"></div>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
      {statCards.map((card) => (
        <div
          key={card.label}
          className={`bg-white border ${card.borderColor} rounded-lg p-6 shadow-sm`}
        >
          <p className="text-sm font-medium text-gray-600 mb-1">{card.label}</p>
          <p className={`text-3xl font-semibold ${card.color}`}>{card.value}</p>
        </div>
      ))}
    </div>
  );
}
