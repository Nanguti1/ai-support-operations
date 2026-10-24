import type { TicketStatus, TicketPriority } from '@/lib/types';

interface StatusBadgeProps {
  type: 'status' | 'priority';
  value: TicketStatus | TicketPriority;
}

const statusLabels: Record<TicketStatus, string> = {
  open: 'Open',
  in_progress: 'In progress',
  closed: 'Closed',
};

const priorityLabels: Record<TicketPriority, string> = {
  low: 'Low',
  medium: 'Medium',
  high: 'High',
};

const statusStyles: Record<TicketStatus, string> = {
  open: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  in_progress: 'bg-blue-50 text-blue-700 border-blue-200',
  closed: 'bg-gray-50 text-gray-700 border-gray-200',
};

const priorityStyles: Record<TicketPriority, string> = {
  low: 'bg-slate-50 text-slate-600 border-slate-200',
  medium: 'bg-amber-50 text-amber-700 border-amber-200',
  high: 'bg-red-50 text-red-700 border-red-200',
};

export function StatusBadge({ type, value }: StatusBadgeProps) {
  const labels = type === 'status' ? statusLabels : priorityLabels;
  const styles = type === 'status' ? statusStyles : priorityStyles;

  return (
    <span
      className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${styles[value as keyof typeof styles]}`}
    >
      {labels[value as keyof typeof labels]}
    </span>
  );
}
