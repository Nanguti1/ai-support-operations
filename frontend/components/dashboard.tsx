'use client';

import { useState, useEffect } from 'react';
import { DashboardHeader } from './dashboard-header';
import { TicketStats } from './ticket-stats';
import { TicketList } from './ticket-list';
import { TicketForm } from './ticket-form';
import { api } from '@/lib/api';
import type { TicketStats as TicketStatsType, PaginatedTicketsResponse } from '@/lib/types';

export function Dashboard() {
  const [stats, setStats] = useState<TicketStatsType>({ open: 0, in_progress: 0, closed: 0 });
  const [tickets, setTickets] = useState<PaginatedTicketsResponse>({
    data: [],
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [isFormOpen, setIsFormOpen] = useState(false);
  const [statusFilter, setStatusFilter] = useState<string>('all');
  const [isLoading, setIsLoading] = useState(true);

  const fetchData = async () => {
    setIsLoading(true);
    try {
      const [statsData, ticketsData] = await Promise.all([
        api.getStats(),
        api.getTickets({ page: 1, limit: 10 }),
      ]);
      setStats(statsData);
      setTickets(ticketsData);
    } catch (error) {
      console.error('Failed to fetch data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleTicketCreated = () => {
    fetchData();
  };

  const handleTicketUpdated = () => {
    fetchData();
  };

  return (
    <>
      <DashboardHeader onNewTicket={() => setIsFormOpen(true)} />
      <TicketStats stats={stats} isLoading={isLoading} />
      <TicketList
        initialTickets={tickets}
        statusFilter={statusFilter}
        onStatusFilterChange={setStatusFilter}
        onTicketUpdate={handleTicketUpdated}
      />
      <TicketForm
        isOpen={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        onSuccess={handleTicketCreated}
      />
    </>
  );
}
