export type TicketStatus = 'open' | 'in_progress' | 'closed';
export type TicketPriority = 'low' | 'medium' | 'high';

export interface Ticket {
  id: number;
  title: string;
  description: string | null;
  status: TicketStatus;
  priority: TicketPriority;
  created_at: string;
}

export interface TicketStats {
  open: number;
  in_progress: number;
  closed: number;
}

export interface PaginatedTicketsResponse {
  data: Ticket[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface CreateTicketRequest {
  title: string;
  description?: string;
  status?: TicketStatus;
  priority?: TicketPriority;
}

export interface UpdateTicketRequest {
  status?: TicketStatus;
  priority?: TicketPriority;
}

export interface ApiError {
  message?: string;
  errors?: Record<string, string[]>;
}
