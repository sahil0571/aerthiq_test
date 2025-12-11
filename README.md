# Financial Management System - React UI

A modern, comprehensive React-based financial management system with full CRUD operations for accounts, transactions, projects, and employees.

## Features

### Core Pages
- **Dashboard**: Overview of financial metrics, project summaries, and recent transactions
- **Accounts**: Manage financial accounts with types (asset, liability, equity, income, expense)
- **Transactions**: Track debits and credits with account, project, and employee associations
- **Projects**: Monitor project financials including budget, income, expenses, and balance
- **Employees**: Manage employee information with salary tracking and outstanding amounts

### Key Functionalities
- Full CRUD operations for all entities
- Financial year and date range filtering
- Project and employee selectors in transactions
- Real-time balance calculations
- Optimistic updates with React Query
- Client-side form validation
- Error handling and loading states
- Responsive design with Tailwind CSS

## Tech Stack

- **React 18** - UI framework
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **React Router** - Navigation
- **TanStack Query (React Query)** - Server state management
- **Axios** - API client
- **Tailwind CSS** - Styling
- **date-fns** - Date formatting
- **Lucide React** - Icons

## Getting Started

### Prerequisites
- Node.js 18+ and npm/yarn

### Installation

```bash
# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Environment Variables

Create a `.env` file in the root directory:

```env
VITE_API_URL=http://localhost:8000
```

The API proxy is configured in `vite.config.ts` to forward `/api` requests to the backend.

## Project Structure

```
src/
├── components/
│   ├── ui/              # Reusable UI components
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   ├── Select.tsx
│   │   ├── DatePicker.tsx
│   │   ├── Modal.tsx
│   │   └── Table.tsx
│   ├── filters/         # Filter components
│   │   └── FilterBar.tsx
│   └── Layout.tsx       # Main layout with navigation
├── pages/
│   ├── Accounts/        # Account pages
│   ├── Transactions/    # Transaction pages
│   ├── Projects/        # Project pages
│   ├── Employees/       # Employee pages
│   └── Dashboard/       # Dashboard page
├── hooks/               # Custom React hooks
│   ├── useAccounts.ts
│   ├── useTransactions.ts
│   ├── useProjects.ts
│   ├── useEmployees.ts
│   └── useDashboard.ts
├── lib/
│   └── api.ts          # API client configuration
├── types/
│   └── index.ts        # TypeScript type definitions
├── App.tsx             # Root component with routes
├── main.tsx            # Application entry point
└── index.css           # Global styles
```

## API Integration

The application expects the following API endpoints:

### Accounts
- `GET /api/accounts` - List accounts
- `GET /api/accounts/:id` - Get account details
- `POST /api/accounts` - Create account
- `PUT /api/accounts/:id` - Update account
- `DELETE /api/accounts/:id` - Delete account

### Transactions
- `GET /api/transactions` - List transactions
- `GET /api/transactions/:id` - Get transaction details
- `POST /api/transactions` - Create transaction
- `PUT /api/transactions/:id` - Update transaction
- `DELETE /api/transactions/:id` - Delete transaction

### Projects
- `GET /api/projects` - List projects
- `GET /api/projects/:id` - Get project details
- `POST /api/projects` - Create project
- `PUT /api/projects/:id` - Update project
- `DELETE /api/projects/:id` - Delete project

### Employees
- `GET /api/employees` - List employees
- `GET /api/employees/:id` - Get employee details
- `POST /api/employees` - Create employee
- `PUT /api/employees/:id` - Update employee
- `DELETE /api/employees/:id` - Delete employee

### Dashboard
- `GET /api/dashboard/summary` - Get dashboard summary

All list endpoints support query parameters for filtering:
- `financial_year` - Filter by financial year (e.g., FY2024)
- `start_date` - Filter by start date
- `end_date` - Filter by end date
- `project_id` - Filter by project
- `employee_id` - Filter by employee
- `account_id` - Filter by account
- `status` - Filter by status
- `search` - Search term
- `page` - Page number
- `size` - Page size

## Features in Detail

### Filtering
- Financial year selector
- Date range picker
- Project dropdown
- Employee dropdown
- Status filters
- Search functionality

### Forms
- Real-time validation
- Error display
- Loading states
- Modal-based editing
- Dropdown selectors for related entities

### Tables
- Sortable columns
- Click-to-view details
- Inline edit/delete actions
- Loading skeletons
- Empty states

### Data Management
- React Query for caching and synchronization
- Automatic refetching after mutations
- Optimistic updates
- Error boundaries

## Development

### Code Style
- ESLint for linting
- TypeScript strict mode
- Consistent component patterns

### Type Checking
```bash
npm run type-check
```

### Linting
```bash
npm run lint
```

## Deployment

The application can be deployed to any static hosting service:

1. Build the application: `npm run build`
2. Deploy the `dist` folder to your hosting service
3. Configure environment variables for the API URL
4. Ensure the API server has CORS enabled for your frontend domain

## Future Enhancements

- [ ] Export data to CSV/Excel
- [ ] Print reports
- [ ] Advanced charts and visualizations
- [ ] Bulk operations
- [ ] User authentication
- [ ] Role-based access control
- [ ] Audit logs
- [ ] Mobile app version

## License

MIT
