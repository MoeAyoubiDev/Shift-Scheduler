# Difference Between Team Leader and Director Dashboard Navigation Logic

## Overview

The Team Leader and Director dashboards use **fundamentally different navigation approaches**, which explains why cards work on click in the Director dashboard but not in the Team Leader dashboard.

---

## Team Leader Dashboard: Single-Page Application (SPA) Approach

### HTML Structure
- **Navigation cards are `<button>` elements** (not links)
- Cards have `data-section` attributes (e.g., `data-section="overview"`, `data-section="shift-requests"`)
- All sections are rendered on the same page but hidden/shown via CSS

**Example from `app/Views/teamleader/dashboard.php`:**
```php
<button type="button" class="nav-card active" data-section="overview">
    <div class="nav-card-icon">...</div>
    <div class="nav-card-content">
        <div class="nav-card-title">Overview</div>
        <div class="nav-card-subtitle">Control center</div>
    </div>
</button>
```

### JavaScript Logic
- JavaScript in `public/assets/js/dashboard.js` listens for clicks on `.nav-card` buttons
- When clicked, it calls `navigateToSection(sectionName)` function
- This function:
  1. Hides all `.dashboard-section` elements
  2. Shows the target section matching `data-section`
  3. Updates active states on navigation cards
  4. Updates URL hash (e.g., `#overview`)

**Key JavaScript code:**
```javascript
document.addEventListener('click', function(e) {
    let navTrigger = target.closest('.nav-card, .quick-action-card, [data-section]');
    
    if (navTrigger && navTrigger.classList.contains('nav-card')) {
        e.preventDefault();
        e.stopPropagation();
        
        const sectionName = navTrigger.getAttribute('data-section');
        if (sectionName) {
            navigateToSection(sectionName, navTrigger);
        }
    }
});
```

### Why Cards Might Not Work
1. **JavaScript not loaded**: If `dashboard.js` fails to load or has errors, buttons won't respond
2. **JavaScript errors**: Any error in the click handler prevents navigation
3. **Missing sections**: If a section with matching `data-section` doesn't exist, nothing happens
4. **CSS conflicts**: If sections aren't properly hidden/shown, navigation appears broken
5. **Event delegation issues**: If the click listener isn't properly attached, clicks are ignored

---

## Director Dashboard: Multi-Page Application (MPA) Approach

### HTML Structure
- **Navigation cards are `<a>` (anchor/link) elements** (not buttons)
- Cards have `href` attributes pointing to different PHP pages
- Each page is a separate PHP file (e.g., `/dashboard/employees.php`, `/dashboard/reports.php`)

**Example from `app/Views/director/partials/topnav.php`:**
```php
<a href="/dashboard/employees.php" class="nav-card director-nav-card">
    <div class="nav-card-icon">...</div>
    <div class="nav-card-content">
        <div class="nav-card-title">Employees</div>
        <div class="nav-card-subtitle">Roster intelligence</div>
    </div>
</a>
```

### Navigation Logic
- **No JavaScript required** for basic navigation
- Clicking a card triggers standard browser navigation (full page load)
- The browser follows the `href` URL naturally
- Each page is independently rendered by PHP

### Why Cards Always Work
1. **Native browser behavior**: HTML links work without JavaScript
2. **No dependency on JS**: Even if JavaScript fails, links still navigate
3. **Server-side routing**: PHP handles page routing, not client-side JavaScript
4. **Reliable**: Standard web navigation pattern that always works

---

## Key Differences Summary

| Aspect | Team Leader | Director |
|--------|------------|----------|
| **Element Type** | `<button>` | `<a>` (link) |
| **Navigation Method** | JavaScript-driven (SPA) | Browser navigation (MPA) |
| **JavaScript Required** | ✅ Yes (mandatory) | ❌ No (optional) |
| **Page Structure** | All sections on one page | Separate PHP pages |
| **URL Changes** | Hash-based (`#section`) | Full URL (`/dashboard/page.php`) |
| **Reliability** | Depends on JS execution | Works without JS |
| **User Experience** | Instant (no page reload) | Page reload required |

---

## Why Team Leader Cards Don't Work

### Common Issues:

1. **JavaScript Not Loaded**
   - Check if `public/assets/js/dashboard.js` is included in the page
   - Verify the script path is correct
   - Check browser console for 404 errors

2. **JavaScript Errors**
   - Open browser console (F12) and check for errors
   - Look for syntax errors, undefined functions, or missing dependencies
   - The click handler might be failing silently

3. **Missing Event Listener**
   - The event listener might not be attached on page load
   - Check if `DOMContentLoaded` event fired correctly
   - Verify the script runs after DOM is ready

4. **Section Elements Missing**
   - Each `data-section` value must have a corresponding `.dashboard-section[data-section="..."]` element
   - If sections aren't rendered, navigation has nowhere to go

5. **CSS Display Issues**
   - Sections might be hidden but not properly shown on navigation
   - Check CSS for `.dashboard-section` and `.dashboard-section.active` rules

---

## How to Fix Team Leader Navigation

### Step 1: Verify JavaScript is Loaded
```html
<!-- Check if this is in the page -->
<script src="/assets/js/dashboard.js"></script>
```

### Step 2: Check Browser Console
Open browser DevTools (F12) and look for:
- JavaScript errors
- Missing file errors (404)
- Console warnings

### Step 3: Verify Section Elements Exist
```javascript
// Run in browser console
document.querySelectorAll('.dashboard-section').forEach(section => {
    console.log('Section:', section.getAttribute('data-section'));
});
```

### Step 4: Test Click Handler
```javascript
// Run in browser console
document.querySelector('.nav-card').addEventListener('click', function() {
    console.log('Card clicked!');
});
```

### Step 5: Check navigateToSection Function
```javascript
// Run in browser console
if (typeof navigateToSection === 'function') {
    console.log('navigateToSection exists');
    navigateToSection('overview');
} else {
    console.error('navigateToSection is not defined');
}
```

---

## Recommendation

If Team Leader navigation continues to fail, consider:

1. **Switch to Director's approach**: Convert Team Leader cards to `<a>` links pointing to separate PHP pages
2. **Debug JavaScript**: Fix the existing JavaScript to ensure it works reliably
3. **Hybrid approach**: Use links but add JavaScript for smooth transitions (progressive enhancement)

The Director approach is more reliable because it doesn't depend on JavaScript, but the Team Leader approach provides a smoother, faster user experience when it works.

---

## Files Involved

### Team Leader Dashboard
- `app/Views/teamleader/dashboard.php` - Main dashboard template
- `public/assets/js/dashboard.js` - Navigation JavaScript logic

### Director Dashboard
- `app/Views/director/dashboard.php` - Main dashboard template
- `app/Views/director/partials/topnav.php` - Navigation cards
- `app/Views/director/pages/*.php` - Individual page files

---

## Conclusion

**Director cards work because they're HTML links** - the browser handles navigation automatically.

**Team Leader cards require JavaScript** - if the JavaScript fails to load or execute, the buttons do nothing.

This is the fundamental architectural difference between the two dashboards.

