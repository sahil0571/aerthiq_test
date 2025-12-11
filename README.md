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

## Laravel API Backend

The application is backed by a comprehensive Laravel 10 API with full CRUD operations, advanced reporting, and real-time balance calculations.

### API Features

- **RESTful Controllers**: Full CRUD operations for all entities
- **Form Request Validation**: Comprehensive validation for all endpoints
- **Service Layer**: Business logic separation with dedicated service classes
- **Resource Transformers**: JSON responses with computed fields
- **Real-time Calculations**: Automatic balance recalculation on transaction changes
- **Financial Year Filtering**: Comprehensive filtering by FY, date ranges, and entity relationships
- **Reporting Endpoints**: Advanced financial reports and summaries

### API Endpoints Documentation

#### Account Management
```
GET    /api/accounts              - List accounts with filtering
POST   /api/accounts              - Create new account
GET    /api/accounts/{id}         - Get account details with balance
PUT    /api/accounts/{id}         - Update account
DELETE /api/accounts/{id}         - Delete account
```

#### Transaction Management
```
GET    /api/transactions          - List transactions with FY/date filters
POST   /api/transactions          - Create transaction (recalculates balances)
GET    /api/transactions/{id}     - Get transaction details
PUT    /api/transactions/{id}     - Update transaction (recalculates balances)
DELETE /api/transactions/{id}     - Delete transaction (recalculates balances)
```

#### Project Management
```
GET    /api/projects              - List projects with status/date filters
POST   /api/projects              - Create new project
GET    /api/projects/{id}         - Get project with financial totals
PUT    /api/projects/{id}         - Update project
DELETE /api/projects/{id}         - Delete project
GET    /api/projects/{id}/summary - Get project financial summary
```

#### Employee Management
```
GET    /api/employees                    - List employees with filters
POST   /api/employees                    - Create new employee
GET    /api/employees/{id}               - Get employee with salary details
PUT    /api/employees/{id}               - Update employee
DELETE /api/employees/{id}               - Delete employee
GET    /api/employees/{id}/salary-history - Get employee salary history
GET    /api/employees/reports/salary     - Get salary report with FY filtering
```

#### Category Management
```
GET    /api/categories          - List categories
POST   /api/categories          - Create new category
GET    /api/categories/{id}     - Get category details
PUT    /api/categories/{id}     - Update category
DELETE /api/categories/{id}     - Delete category
```

#### Deduction Management
```
GET    /api/deductions                     - List deductions with filters
POST   /api/deductions                     - Create new deduction
GET    /api/deductions/{id}                - Get deduction details
PUT    /api/deductions/{id}                - Update deduction
DELETE /api/deductions/{id}                - Delete deduction
GET    /api/deductions/reports/employee-deductions - Get employee deductions report
```

#### Dashboard & Reports
```
GET    /api/dashboard/summary              - Dashboard summary with FY filtering
GET    /api/reports/financial-year/{fy}    - Comprehensive FY report
GET    /api/reports/projects               - Project financial reports
GET    /api/reports/employees/salary       - Employee salary reports
GET    /api/reports/profit-loss            - Profit & loss report
GET    /api/reports/balance-sheet          - Balance sheet report
GET    /api/reports/projects/{id}/financial-summary - Project-specific financial summary
```

### Query Parameters

All list endpoints support comprehensive filtering:

- **Financial Year Filtering**: `?financial_year=FY2024`
- **Date Range**: `?start_date=2024-01-01&end_date=2024-12-31`
- **Entity Relationships**: `?project_id=1&employee_id=2&account_id=3`
- **Type/Status Filters**: `?type=asset&status=active&is_active=true`
- **Search**: `?search=project_name` (searches across name, code, and related fields)
- **Pagination**: `?page=1&size=20` (default size: 15)

### Response Format

All endpoints return JSON responses with the following structure:

```json
{
    "items": [...],           // Array of resources
    "total": 150,             // Total number of records
    "page": 1,                // Current page
    "size": 20,               // Records per page
    "pages": 8                // Total number of pages
}
```

Single resource endpoints return the resource directly:

```json
{
    "id": 1,
    "code": "ACC001",
    "name": "Cash Account",
    "balance": 15000.50,
    "type": "asset",
    "is_active": true,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
}
```

### Validation

All endpoints use Form Request classes for validation:

- **Required Fields**: Validated with meaningful error messages
- **Data Types**: Proper validation for numeric, date, and email fields
- **Unique Constraints**: Unique validation for codes and emails
- **Foreign Key Validation**: Ensures referenced entities exist
- **Business Rules**: End date after start date, minimum amounts, etc.

### Balance Calculations

The API automatically calculates and maintains:

- **Account Balances**: Opening balance + all transactions
- **Project Totals**: Income and expenses with computed balance
- **Employee Outstanding**: Expected vs. paid salary calculations
- **Financial Summaries**: Assets, liabilities, equity, income, expenses

### Laravel Backend Structure

```
laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # API Controllers
│   │   ├── Requests/        # Form Request validation
│   │   └── Resources/       # JSON transformers
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── migrations/          # Database schema
│   └── factories/           # Test data factories
├── routes/
│   └── api.php             # API routes definition
└── tests/
    └── Feature/            # API feature tests
```

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
