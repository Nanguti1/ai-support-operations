# Mini Ticket Tracker

A full-stack ticket management application built with Laravel, Next.js,
TypeScript, MySQL, and Tailwind CSS. It provides a REST API for creating
and managing tickets, plus a responsive web interface for viewing,
filtering, creating, and updating tickets.

## Live Demo

-   **Application:** https://lodwarservices-sigma.vercel.app/
-   **Backend API:** https://lodwarservices.wanyonyi.co.ke

## Features

-   Create tickets with a title, optional description, status, and
    priority.
-   List tickets through a paginated API.
-   Filter tickets by status.
-   Update a ticket's status or priority.
-   View ticket counts grouped by status, including statuses with zero
    tickets.
-   Validate incoming API requests and return JSON responses.
-   Run backend feature tests for key API behaviour.

## Technology Stack

**Backend:** PHP 8.3+, Laravel 13, MySQL, Eloquent ORM, Laravel
validation, PHPUnit / Laravel feature tests.

**Frontend:** Next.js, React, TypeScript, Tailwind CSS.

## Repository Structure

``` text
mini-ticket-tracker/
├── backend/     # Laravel REST API
├── frontend/    # Next.js web application
├── README.md
└── .gitignore
```

## Local Setup

### Prerequisites

-   PHP 8.3 or later
-   Composer
-   Node.js 20 or later and npm
-   MySQL

### 1. Set up the backend

From the repository root:

``` bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Update the database settings in `backend/.env` to match your local MySQL
database:

``` dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_ticket_tracker
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Create the database in MySQL, then run the migrations and start the API:

``` bash
php artisan migrate
php artisan serve
```

By default, the API is available at `http://127.0.0.1:8000`.

### 2. Set up the frontend

Open a second terminal from the repository root:

``` bash
cd frontend
npm install
cp .env.example .env
```

Set the API base URL in `frontend/.env`:

``` dotenv
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000
```

Start the development server:

``` bash
npm run dev
```

Open the local URL printed by Next.js, typically
`http://localhost:3000`.

## API Reference

Production API base URL:

``` text
https://lodwarservices.wanyonyi.co.ke/api
```

  ------------------------------------------------------------------------------------------
  Method                  Endpoint                                   Description
  ----------------------- ------------------------------------------ -----------------------
  `GET`                   `/tickets?page=1&limit=10&status=closed`   List tickets with
                                                                     pagination and optional
                                                                     status filtering

  `POST`                  `/tickets`                                 Create a ticket

  `PATCH`                 `/tickets/{id}`                            Update a ticket's
                                                                     title, description,
                                                                     status, and/or priority

  `GET`                   `/tickets/stats`                           Get ticket counts
                                                                     grouped by status
  ------------------------------------------------------------------------------------------

### List, filter, and paginate tickets

``` http
GET /api/tickets?page=1&limit=10&status=closed
```

-   `page`: page number, starting at `1`.
-   `limit`: number of tickets per page; the API caps this at `100`.
-   `status`: optional status filter. Supported values are `open`,
    `in_progress`, and `closed`.

Live example: [Filter closed tickets and retrieve page
1](https://lodwarservices.wanyonyi.co.ke/api/tickets?page=1&limit=10&status=closed)

### Create a ticket

``` http
POST /api/tickets
Content-Type: application/json
```

Example request body:

``` json
{
  "title": "Investigate login issue",
  "description": "Some users cannot sign in.",
  "status": "open",
  "priority": "high"
}
```

The title is required and the description is optional. Supported
statuses are `open`, `in_progress`, and `closed`; supported priorities
are `low`, `medium`, and `high`. If status and priority are omitted, the
API applies its defaults.

### Update a ticket

``` http
PATCH /api/tickets/1
Content-Type: application/json
```

Example request body:

``` json
{
  "title": "Updated title",
  "description": "Updated description",
  "status": "in_progress",
  "priority": "high"
}
```

Send any of the supported fields: `title`, `description`, `status`, and/or
`priority`. Supported statuses are `open`, `in_progress`, and `closed`;
supported priorities are `low`, `medium`, and `high`.

### Ticket statistics

``` http
GET /api/tickets/stats
```

Live endpoint: [View ticket
statistics](https://lodwarservices.wanyonyi.co.ke/api/tickets/stats)

The response reports ticket counts grouped by status and includes
supported statuses even when their count is zero.

### Live API links

-   [List tickets](https://lodwarservices.wanyonyi.co.ke/api/tickets)
-   [Filter closed tickets with
    pagination](https://lodwarservices.wanyonyi.co.ke/api/tickets?page=1&limit=10&status=closed)
-   [Ticket
    statistics](https://lodwarservices.wanyonyi.co.ke/api/tickets/stats)

Creating and updating tickets require `POST` or `PATCH` requests with a
JSON body; opening those endpoints as browser links does not perform
those actions.

## Running Tests and Checks

### Backend tests

From the repository root:

``` bash
cd backend
php artisan test
```

The feature tests cover core ticket API behaviour, including creation,
validation, pagination, status filtering, status/priority updates,
statistics, and not-found cases.

### Frontend checks

From the repository root:

``` bash
cd frontend
npm run lint
npm run build
```

## Decisions and Trade-offs

-   **Laravel for the API:** Routing, request validation, Eloquent ORM,
    and testing support keep the API structured without unnecessary
    layers.
-   **Enums for ticket fields:** Status and priority are constrained to
    supported options, making the domain rules easier to understand and
    maintain.
-   **Database-backed pagination:** The API paginates results instead of
    loading every ticket at once. The page size is capped to limit
    unnecessarily large responses.
-   **Focused update endpoint:** The update endpoint accepts title,
    description, status, and priority fields, providing flexibility for
    ticket updates while maintaining clear field constraints.
-   **Next.js and TypeScript:** The frontend benefits from typed data
    and reusable React components while keeping the interface
    straightforward.
-   **Local component state:** React state is sufficient for the current
    UI, so a global state library or separate server-state dependency
    was not introduced.
-   **Tailwind CSS:** Utility classes provide responsive styling without
    adding a larger component framework.
-   **Simple ticket creation flow:** A focused form/modal keeps ticket
    creation accessible without adding another workflow.
-   **No authentication:** Authentication and authorization were left
    out because they were not part of the assignment scope. A production
    system handling real users or sensitive tickets should add them.
-   **CORS configuration:** Local development requires the frontend to
    call the API from another origin. Production CORS origins should be
    restricted to the intended frontend domain.
-   **Fresh statistics:** Statistics are requested from the API when
    needed rather than adding caching and invalidation complexity for
    this small application.

## Scope and Production Considerations

This project focuses on the requested ticket-management workflow. Before
using it as a production service for real operational data, add
authentication and authorization, restrict CORS to trusted origins, and
consider logging, monitoring, rate limiting, and deployment-specific
secrets management.

## Author

**Kevin W. Wanyonyi**

-   GitHub: https://github.com/Nanguti1
-   Website: https://wanyonyi.co.ke
