# Migration Checklist

## ✅ Completed

- [x] Created new directory structure
- [x] Moved header.php to includes/
- [x] Moved footer.php to includes/
- [x] Moved scripts to scripts/
- [x] Moved documentation to docs/
- [x] Created API endpoint placeholders
- [x] Created middleware files
- [x] Updated public/index.php paths
- [x] Updated includes/header.php paths
- [x] Updated includes/footer.php paths

## 🔄 In Progress

- [ ] Update all render_view() calls to use new paths
- [ ] Update app/Helpers/view.php to handle both old and new paths
- [ ] Test all pages after path updates
- [ ] Update deployment scripts paths

## 📋 Remaining Tasks

### Path Updates Needed

1. **app/Helpers/view.php**
   - Update to check both old and new view locations
   - Add backward compatibility

2. **All Controller Files**
   - Check for any direct includes of partials
   - Update if needed

3. **View Files**
   - Check for any relative path references
   - Update asset paths if needed

### Testing Required

- [ ] Login page
- [ ] Director dashboard
- [ ] Team Leader dashboard
- [ ] Supervisor dashboard
- [ ] Senior dashboard
- [ ] Employee dashboard
- [ ] All forms and actions
- [ ] API endpoints (when implemented)

### Documentation Updates

- [ ] Update README.md with new structure
- [ ] Update deployment guide
- [ ] Create API documentation
- [ ] Update development guide

### Deployment Updates

- [ ] Update deploy.sh with new paths
- [ ] Update web server configuration examples
- [ ] Test deployment on staging

## Notes

- Old `app/Views/partials/` directory is now empty but kept for reference
- All includes now use the new `includes/` directory
- API endpoints are placeholders and need implementation
- Dashboard files in `public/dashboard/` need to be created

## Rollback Plan

If issues occur, the old structure can be restored by:
1. Moving files back from `includes/` to `app/Views/partials/`
2. Reverting path changes in `public/index.php`
3. Restoring original file locations

