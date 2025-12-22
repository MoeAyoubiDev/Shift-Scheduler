# Multi-Tenant SaaS Implementation Guide

## Overview

This document outlines the complete multi-tenant SaaS transformation of the Shift Scheduler platform.

## Architecture Changes

### Database Schema

1. **Companies Table**: Core tenant isolation
   - Stores company information
   - Tracks onboarding progress
   - Manages payment status
   - Email verification

2. **Company ID Addition**: All relevant tables now include `company_id`
   - `sections` - Company-specific sections
   - `users` - Company-scoped users
   - `weeks` - Company-specific weeks
   - `schedules`, `shift_requirements`, `notifications` - All company-scoped

### Data Isolation

- **Strict Isolation**: Every query must filter by `company_id`
- **Foreign Keys**: All relationships include company context
- **Unique Constraints**: Updated to include `company_id` (e.g., `username` + `company_id`)

## User Flow

### 1. Public Landing Page
- **URL**: `/` or `/index.php`
- **Content**: Features, benefits, CTAs
- **Actions**: "Sign Up" → `/signup.php`, "Login" → `/login.php`

### 2. Company Sign-Up
- **URL**: `/signup.php`
- **Fields**: Company name, admin email, password, timezone, country, size
- **Validation**: Email uniqueness, password strength
- **Result**: Creates company with `PENDING_VERIFICATION` status
- **Next**: Email verification

### 3. Email Verification
- **URL**: `/verify-email.php?token=...`
- **Process**: Verifies email token
- **Result**: Updates status to `VERIFIED`
- **Next**: Onboarding wizard

### 4. Onboarding Wizard
- **URL**: `/onboarding.php?step=1`
- **Steps**:
  1. Company Details (industry, address)
  2. Work Rules (shift duration, work days, breaks)
  3. Initial Employees (add first team members)
  4. Scheduling Preferences (auto-generate, notifications)
  5. Review & Confirm
- **Progress**: Saved after each step
- **Result**: Status → `PAYMENT_PENDING`
- **Next**: Preview prototype

### 5. Preview Prototype
- **URL**: `/onboarding-preview.php`
- **Content**: Live preview of dashboard with example data
- **Action**: Company confirms setup
- **Next**: Payment

### 6. Payment
- **URL**: `/payment.php`
- **Type**: One-time payment
- **Integration**: Payment provider (Stripe/PayPal/etc.)
- **Result**: Status → `ACTIVE`, unlocks dashboard
- **Next**: Full dashboard access

### 7. Dashboard Access
- **URL**: `/index.php`
- **Access**: Only if `status = 'ACTIVE'`
- **Content**: Full functionality based on role

## Implementation Status

### ✅ Completed
- [x] Database migrations (companies table, company_id columns)
- [x] Landing page design
- [x] Sign-up page
- [x] Email verification page
- [x] Onboarding wizard (5 steps)
- [x] Company model
- [x] BaseModel query method

### 🚧 In Progress
- [ ] Preview prototype page
- [ ] Payment integration
- [ ] Authentication update (company context)
- [ ] Multi-tenant query updates (all models)
- [ ] Company middleware

### ⏳ Pending
- [ ] Email service integration
- [ ] Payment provider integration
- [ ] Company-scoped stored procedures
- [ ] Data migration script (existing data → company)
- [ ] Testing & validation

## Next Steps

1. **Complete Preview Page**: Show live dashboard preview
2. **Payment Integration**: Add Stripe/PayPal hooks
3. **Update Authentication**: Include company_id in login
4. **Refactor Models**: Add company filtering to all queries
5. **Update Controllers**: Ensure company context in all actions
6. **Add Middleware**: Company access validation
7. **Email Service**: Send verification emails
8. **Testing**: Comprehensive multi-tenant testing

## Security Considerations

- **Data Isolation**: Never allow cross-company data access
- **Company Validation**: Verify company_id on every request
- **Payment Security**: Secure payment token handling
- **Email Verification**: Prevent account hijacking
- **Session Security**: Include company_id in session

## Migration Path

For existing single-tenant installations:

1. Run migrations to add companies table
2. Create a default company for existing data
3. Update all existing records with default company_id
4. Test data isolation
5. Deploy new sign-up flow

## Notes

- All existing functionality preserved
- Design system maintained
- No breaking changes to UI/UX
- Backward compatible with proper migration

