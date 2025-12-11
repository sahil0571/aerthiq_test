import { forwardRef } from 'react'
import Input from './Input'

interface DatePickerProps {
  label?: string
  error?: string
  value?: string
  onChange?: (value: string) => void
  className?: string
  placeholder?: string
}

const DatePicker = forwardRef<HTMLInputElement, DatePickerProps>(
  ({ label, error, value, onChange, className = '', placeholder, ...props }, ref) => {
    return (
      <Input
        ref={ref}
        type="date"
        label={label}
        error={error}
        value={value}
        onChange={(e) => onChange?.(e.target.value)}
        className={className}
        placeholder={placeholder}
        {...props}
      />
    )
  }
)

DatePicker.displayName = 'DatePicker'

export default DatePicker
