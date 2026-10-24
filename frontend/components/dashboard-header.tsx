'use client';

interface DashboardHeaderProps {
  onNewTicket: () => void;
}

export function DashboardHeader({ onNewTicket }: DashboardHeaderProps) {
  return (
    <div className="mb-8">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-gray-900">Lodwar Services</h1>
          <h2 className="text-xl font-medium text-gray-700 mt-1">Support tickets</h2>
          <p className="text-sm text-gray-500 mt-1">
            Manage, prioritize, and track customer issues.
          </p>
        </div>
        <button
          onClick={onNewTicket}
          className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors cursor-pointer"
        >
          New ticket
        </button>
      </div>
    </div>
  );
}
