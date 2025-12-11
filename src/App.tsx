import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import Layout from './components/Layout'
import AccountsPage from './pages/Accounts/AccountsPage'
import AccountDetailPage from './pages/Accounts/AccountDetailPage'
import TransactionsPage from './pages/Transactions/TransactionsPage'
import TransactionDetailPage from './pages/Transactions/TransactionDetailPage'
import ProjectsPage from './pages/Projects/ProjectsPage'
import ProjectDetailPage from './pages/Projects/ProjectDetailPage'
import EmployeesPage from './pages/Employees/EmployeesPage'
import EmployeeDetailPage from './pages/Employees/EmployeeDetailPage'
import DashboardPage from './pages/Dashboard/DashboardPage'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Layout />}>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<DashboardPage />} />
          
          <Route path="accounts">
            <Route index element={<AccountsPage />} />
            <Route path=":id" element={<AccountDetailPage />} />
          </Route>
          
          <Route path="transactions">
            <Route index element={<TransactionsPage />} />
            <Route path=":id" element={<TransactionDetailPage />} />
          </Route>
          
          <Route path="projects">
            <Route index element={<ProjectsPage />} />
            <Route path=":id" element={<ProjectDetailPage />} />
          </Route>
          
          <Route path="employees">
            <Route index element={<EmployeesPage />} />
            <Route path=":id" element={<EmployeeDetailPage />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
