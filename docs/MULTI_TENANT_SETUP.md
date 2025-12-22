# Multi-Tenant SaaS Setup Guide

## Quick Start

### 1. Run Database Migrations

```bash
# Run migrations in order
mysql -u root -p ShiftSchedulerDB < database/migrations/001_add_companies_table.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/002_add_company_id_to_tables.sql
mysql -u root -p ShiftSchedulerDB < database/migrations/003_update_stored_procedures.sql
```

### 2. Configure Email Service

Update email configuration in `config/app.php` or environment variables:
- SMTP host
- SMTP port
- SMTP username/password
- From email address

### 3. Configure Payment Provider

Add payment provider credentials (Stripe/PayPal):
- API keys
- Webhook endpoints
- Payment amount

### 4. Test the Flow

1. Visit `/` - See landing page
2. Click "Sign Up" - Create company account
3. Verify email - Check inbox (or use token from database)
4. Complete onboarding - 5-step wizard
5. Preview dashboard - Review setup
6. Complete payment - Activate account
7. Login - Access full dashboard

## File Structure

### New Files Created

```
public/
  ├── signup.php              # Company registration
  ├── login.php               # Login page
  ├── verify-email.php        # Email verification
  ├── onboarding.php          # Onboarding wizard
  ├── onboarding-preview.php  # Preview prototype
  └── payment.php             # Payment page

app/
  └── Models/
      └── Company.php         # Company model

app/Views/
  └── public/
      └── landing.php         # Landing page

database/migrations/
  ├── 001_add_companies_table.sql
  ├── 002_add_company_id_to_tables.sql
  └── 003_update_stored_procedures.sql
```

## Implementation Status

### ✅ Completed
- Database migrations
- Landing page
- Sign-up flow
- Email verification page
- Onboarding wizard (5 steps)
- Preview page
- Payment page (basic)
- Company model

### ⚠️ Requires Configuration
- Email service (SMTP)
- Payment provider integration
- Company-scoped authentication
- Multi-tenant query updates

### 🔄 Next Steps
1. Integrate email service for verification emails
2. Integrate payment provider (Stripe/PayPal)
3. Update authentication to include company_id
4. Refactor all models for company filtering
5. Add company middleware
6. Update all stored procedures
7. Test data isolation

## Important Notes

- **Existing Data**: Run migration script to assign existing data to a default company
- **Email Verification**: Currently uses database tokens; integrate email service
- **Payment**: Currently simulated; integrate real payment provider
- **Authentication**: Needs update to include company context
- **Data Isolation**: All queries must filter by company_id

## Security Checklist

- [ ] Email verification enforced
- [ ] Payment verification implemented
- [ ] Company isolation in all queries
- [ ] Company middleware active
- [ ] Session includes company_id
- [ ] CSRF protection on all forms
- [ ] Input validation on all inputs

