'use client';

import { useState, useEffect } from 'react';
import type { Ticket, TicketStatus, PaginatedTicketsResponse } from '@/lib/types';
import { api } from '@/lib/api';
import { StatusBadge } from './status-badge';

interface TicketListProps {
  initialTickets: PaginatedTicketsResponse;
  statusFilter?: string;
  onStatusFilterChange: (status: string) => void;
  onTicketUpdate: () => void;
}

export function TicketList({
  initialTickets,
  statusFilter,
  onStatusFilterChange,
  onTicketUpdate,
}: TicketListProps) {
  const [tickets, setTickets] = useState<PaginatedTicketsResponse>(initialTickets);
  const [currentPage, setCurrentPage] = useState(initialTickets.current_page);
  const [isLoading, setIsLoading] = useState(false);
  const [updatingTicketId, setUpdatingTicketId] = useState<number | null>(null);

  // Sync state with prop when it changes
  useEffect(() => {
    setTickets(initialTickets);
    setCurrentPage(initialTickets.current_page);
  }, [initialTickets]);

  const fetchTickets = async (page: number, status?: string) => {
    setIsLoading(true);
    try {
      const response = await api.getTickets({ page, limit: 10, status });
      setTickets(response);
    } catch (error) {
      console.error('Failed to fetch tickets:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleStatusFilterChange = (newStatus: string) => {
    onStatusFilterChange(newStatus);
    setCurrentPage(1);
    fetchTickets(1, newStatus === 'all' ? undefined : newStatus);
  };

  const handlePageChange = (page: number) => {
    setCurrentPage(page);
    fetchTickets(page, statusFilter === 'all' ? undefined : statusFilter);
  };

  const handleTicketStatusUpdate = async (ticketId: number, newStatus: TicketStatus) => {
    setUpdatingTicketId(ticketId);
    try {
      await api.updateTicket(ticketId, { status: newStatus });
      onTicketUpdate();
      await fetchTickets(currentPage, statusFilter === 'all' ? undefined : statusFilter);
    } catch (error) {
      console.error('Failed to update ticket:', error);
    } finally {
      setUpdatingTicketId(null);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  if (isLoading && tickets.data.length === 0) {
    return (
      <div className="bg-white border border-gray-200 rounded-lg p-8">
        <div className="animate-pulse space-y-4">
          {[1, 2, 3, 4, 5].map((i) => (
            <div key={i} className="h-16 bg-gray-100 rounded"></div>
          ))}
        </div>
      </div>
    );
  }

  if (tickets.data.length === 0) {
    return (
      <div className="bg-white border border-gray-200 rounded-lg shadow-sm">
        {/* Filters - Always visible */}
        <div className="border-b border-gray-200 p-4">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div className="flex items-center gap-2">
              <label htmlFor="status-filter" className="text-sm font-medium text-gray-700">
                Status:
              </label>
              <select
                id="status-filter"
                value={statusFilter || 'all'}
                onChange={(e) => handleStatusFilterChange(e.target.value)}
                className="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border cursor-pointer"
              >
                <option value="all">All statuses</option>
                <option value="open">Open</option>
                <option value="in_progress">In progress</option>
                <option value="closed">Closed</option>
              </select>
            </div>
            <div className="text-sm text-gray-500">
              Showing 0 of {tickets.total} tickets
            </div>
          </div>
        </div>

        {/* Empty state */}
        <div className="p-12 text-center">
          <div className="text-gray-400 mb-4">
            <svg
              className="mx-auto h-12 w-12"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1.5}
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
          <h3 className="text-lg font-medium text-gray-900 mb-2">No tickets found</h3>
          <p className="text-gray-500 mb-4">
            {statusFilter && statusFilter !== 'all'
              ? 'No tickets match the current filter.'
              : 'Get started by creating your first ticket.'}
          </p>
        </div>
      </div>
    );
  }

  return (
    <div className="bg-white border border-gray-200 rounded-lg shadow-sm">
      {/* Filters */}
      <div className="border-b border-gray-200 p-4">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div className="flex items-center gap-2">
            <label htmlFor="status-filter" className="text-sm font-medium text-gray-700">
              Status:
            </label>
            <select
              id="status-filter"
              value={statusFilter || 'all'}
              onChange={(e) => handleStatusFilterChange(e.target.value)}
              className="block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border cursor-pointer"
            >
              <option value="all">All statuses</option>
              <option value="open">Open</option>
              <option value="in_progress">In progress</option>
              <option value="closed">Closed</option>
            </select>
          </div>
          <div className="text-sm text-gray-500">
            Showing {tickets.data.length} of {tickets.total} tickets
          </div>
        </div>
      </div>

      {/* Ticket List */}
      <div className="divide-y divide-gray-200">
        {tickets.data.map((ticket) => (
          <div key={ticket.id} className="p-4 hover:bg-gray-50 transition-colors">
            <div className="flex flex-col lg:flex-row lg:items-start gap-4">
              <div className="flex-1 min-w-0">
                <div className="flex items-start gap-3">
                  <div className="flex-1">
                    <h3 className="text-sm font-medium text-gray-900">{ticket.title}</h3>
                    {ticket.description && (
                      <p className="text-sm text-gray-500 mt-1 line-clamp-2">
                        {ticket.description}
                      </p>
                    )}
                  </div>
                </div>
                <div className="flex flex-wrap items-center gap-2 mt-2">
                  <StatusBadge type="priority" value={ticket.priority} />
                  <StatusBadge type="status" value={ticket.status} />
                  <span className="text-xs text-gray-400">
                    {formatDate(ticket.created_at)}
                  </span>
                </div>
              </div>
              <div className="flex-shrink-0">
                <select
                  value={ticket.status}
                  onChange={(e) =>
                    handleTicketStatusUpdate(ticket.id, e.target.value as TicketStatus)
                  }
                  disabled={updatingTicketId === ticket.id}
                  className="block w-full pl-3 pr-8 py-1.5 text-sm border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md border disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <option value="open">Open</option>
                  <option value="in_progress">In progress</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Pagination */}
      {tickets.last_page > 1 && (
        <div className="border-t border-gray-200 p-4">
          <div className="flex items-center justify-between">
            <button
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1 || isLoading}
              className="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white cursor-pointer"
            >
              Previous
            </button>
            <div className="flex items-center gap-2">
              <span className="text-sm text-gray-700">
                Page {currentPage} of {tickets.last_page}
              </span>
            </div>
            <button
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === tickets.last_page || isLoading}
              className="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white cursor-pointer"
            >
              Next
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
