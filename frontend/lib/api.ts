import type {
  Ticket,
  TicketStats,
  PaginatedTicketsResponse,
  CreateTicketRequest,
  UpdateTicketRequest,
  ApiError,
} from './types';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

async function handleResponse<T>(response: Response): Promise<T> {
  if (!response.ok) {
    const error: ApiError = await response.json().catch(() => ({}));
    throw error;
  }
  return response.json();
}

export const api = {
  async getStats(): Promise<TicketStats> {
    const response = await fetch(`${API_URL}/api/tickets/stats`);
    return handleResponse<TicketStats>(response);
  },

  async getTickets(params: {
    page?: number;
    limit?: number;
    status?: string;
  } = {}): Promise<PaginatedTicketsResponse> {
    const searchParams = new URLSearchParams();
    if (params.page) searchParams.append('page', params.page.toString());
    if (params.limit) searchParams.append('limit', params.limit.toString());
    if (params.status) searchParams.append('status', params.status);

    const response = await fetch(
      `${API_URL}/api/tickets?${searchParams.toString()}`
    );
    return handleResponse<PaginatedTicketsResponse>(response);
  },

  async createTicket(data: CreateTicketRequest): Promise<Ticket> {
    const response = await fetch(`${API_URL}/api/tickets`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    return handleResponse<Ticket>(response);
  },

  async updateTicket(id: number, data: UpdateTicketRequest): Promise<Ticket> {
    const response = await fetch(`${API_URL}/api/tickets/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    return handleResponse<Ticket>(response);
  },
};
