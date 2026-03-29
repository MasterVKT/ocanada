# Phase 2 Quick Start Guide - Visitor & Presence Management

## Prerequisites
- CodeIgniter 4.5.x installed
- Database `ocanada_db` created
- All Phase 0-1 migrations completed
- Application running on `http://localhost:8080`

## Quick Test Workflow (5 minutes)

### 1. Register a Test Visitor (1 min)
1. Open browser: `http://localhost:8080/visitor/index`
2. Fill form:
   - Nom: `Dupont`
   - Prénom: `Jean`
   - Email: `jean@example.com`
   - Téléphone: `+237 678 123 456`
   - Motif: `Reunion`
   - Personne à voir: `Directeur RH`
3. Click: `Enregistrer l'arrivée`
4. **Expected**: Success message showing badge ID (VIS format)
5. **Verify**: Visitor appears in sidebar "Visiteurs actuels"

### 2. View Badge & History (1 min)
1. Click: `Historique des visites` button
2. See visitor with:
   - Badge ID (VIS format)
   - Status: "Présent"
   - Duration: X min
3. **Verify**: Date filters work, pagination functional

### 3. Checkout Visitor (1 min)
1. Go back to `/visitor/index`
2. Click: `Départ` button on the visitor card
3. **Expected**: Success message, visitor removed from current list
4. Go to history: visitor now shows status "Parti" + departure time

### 4. Test Presence Management (1 min)
1. Log in as admin: `http://localhost:8080/login`
   - Email: `admin@ocanada.local` (from InitialDataSeeder)
   - Password: `password` (default)
2. Navigate: `/admin/presences/index`
3. **Expected**: Today's presences display with stats cards
4. Click: `Corriger` on any presence
5. Change status, add motif, submit
6. **Verify**: "Corrigé" badge appears after reload

### 5. View Statistics (1 min)
1. Click: `Statistiques` button
2. **Expected**: 
   - Daily stats table with presence breakdown
   - Employee rankings by tardiness (top 20)
   - Percentage calculations
3. **Verify**: Date range filter works

## Full Testing Scenarios (30 minutes)

### Scenario A: Complete Visitor Lifecycle
**Objective**: Test visitor from registration to history export

**Steps**:
1. Register visitor at `/visitor/index`
   - Try invalid email → see validation error
   - Try phone without country code → see validation error
   - Enter valid data → success
2. View visitor in sidebar + check badge format
3. Wait 2+ minutes, check visitor duration updates in list
4. Click `Départ` → status changes to "Parti"
5. Navigate to `/visitor/history`
   - Filter by date range (include today)
   - Verify pagination shows >= 1 visitor
   - Check visitor shows departure time
6. Click `/visitor/statistics`
   - Verify motif breakdown
   - Check percentage calculations

**Expected Outcomes**:
- ✅ Form validation works
- ✅ Badge auto-generated in VIS format
- ✅ Duration calculation accurate
- ✅ Status transitions work (present → departi)
- ✅ History filterable by date
- ✅ Statistics calculations correct

---

### Scenario B: Presence Correction Workflow
**Objective**: Test admin ability to correct employee presences

**Prerequisites**: 
- At least 1 employee created (from Phase 1)
- At least 1 presence record for employee (can create via kiosk or manually)

**Steps**:
1. Log in as admin
2. Navigate to `/admin/presences/index`
3. See today's presences with statut cards
4. Click `Corriger` on any presence
5. In modal:
   - Change status from present → retard
   - Enter motif: "Traffic"
   - Submit
6. **Expected**: Modal closes, page refreshes
7. Verify presence row shows:
   - New status (retard = yellow badge)
   - "Corrigé" badge added
8. Navigate to `/admin/presences/history`
   - Filter by employee from step 3
   - Find corrected presence
   - See audit indicator

**Expected Outcomes**:
- ✅ Correction modal opens/closes cleanly
- ✅ Status persists after refresh
- ✅ "Corrigé" indicator appears
- ✅ History shows corrected presence
- ✅ Audit log contains CORRECTION_POINTAGE entry

---

### Scenario C: Realtime Dashboard (AJAX)
**Objective**: Test realtime data endpoints and dashboard refresh

**Prerequisites**: 
- Multiple employees
- Some with presences recorded today
- Admin logged in

**Steps**:
1. Navigate to `/admin/dashboard`
2. **Expected**: Page loads with KPI cards
3. Open browser Network tab (F12)
4. Click: `Actualiser` button
5. **Watch Network tab**: Should see AJAX calls to:
   - `/api/presences/today`
   - `/api/visiteurs/presents` (may return empty)
   - `/api/presences/absents/today`
6. **Expected**: API responses are JSON with:
   - `success: true`
   - `presences: []` or `[{...}, ...]`
   - `count: N`
7. Wait 30 seconds without clicking refresh
8. **Expected**: Auto-refresh triggers (check Network tab)
9. Verify data updates

**Expected Outcomes**:
- ✅ APIs return valid JSON
- ✅ Dashboard displays API data
- ✅ Manual refresh works
- ✅ Auto-refresh (30s) works
- ✅ Network tab shows clean requests (no errors)

---

### Scenario D: CRON Command Testing
**Objective**: Test manual CRON command execution

**Prerequisites**: 
- Terminal/command line access
- 2+ active employees in system
- Ensure at least 1 employee has NO presence record today

**Steps**:
1. Open terminal, navigate to project: `cd c:\ocanada`
2. Run command: `php spark ocanada:mark-absences`
3. **Expected Output**:
   ```
   ✓ Marked 1 employees as absent for 2024-03-09
   ```
4. Verify in database:
   ```sql
   SELECT * FROM presences WHERE DATE(date_pointage) = CURDATE();
   ```
5. **Expected**: New records with `statut='absent'` for previously absent employees
6. Check audit log:
   ```sql
   SELECT * FROM audit_log WHERE event_type = 'ABSENCE_AUTO_MARQUEE' ORDER BY date_creation DESC LIMIT 5;
   ```
7. **Expected**: Entries logged for marked absences

**Expected Outcomes**:
- ✅ Command executes successfully
- ✅ Absence records created
- ✅ Audit log populated
- ✅ Notifications sent to admins

---

## Troubleshooting

### Issue: Visitor form validation errors on every submit
**Solution**: 
- Check email field format (must be valid email)
- Check telephone format (allow +, -, (), spaces; min 7 digits)
- Check name fields (alpha_space only, 2-50 chars)

### Issue: API endpoints return 404
**Solution**:
- Verify Routes.php has API route definitions
- Check controller `app/Controllers/API/RealtimeController.php` exists
- Check method names match route (e.g., `getPresencesToday` for `/api/presences/today`)
- Clear CI4 route cache: `php spark route:cache --remove`

### Issue: Presence correction modal doesn't submit
**Solution**:
- Check browser console (F12) for JavaScript errors
- Verify CSRF token is present in form
- Check that status dropdown has valid value (present/retard/absent)
- Verify user is logged in as admin

### Issue: CRON command not found
**Solution**:
- Verify command file exists: `app/Commands/MarkAbsencesCommand.php`
- Check file namespace: `namespace App\Commands;`
- Check class name: `class MarkAbsencesCommand extends BaseCommand`
- List available commands: `php spark list`

### Issue: Absences not marked when running CRON
**Solution**:
- Verify employees exist and have `statut = 'actif'`
- Verify no presences already recorded for today for those employees
- Check command is running with correct date parameter
- Check audit log for any error messages

---

## Performance Testing

### Before Deploying to Production

**Test 1: Presence History with 1000+ Records**
```php
// Run in database directly or via admin panel
INSERT INTO presences (employe_id, date_pointage, statut, ...) 
SELECT 1, DATE_SUB(NOW(), INTERVAL 1000 DAY), 'present', ... LIMIT 1000;

// Then:
// 1. Navigate to /admin/presences/history
// 2. Check page load time (should be < 2s)
// 3. Verify pagination works smoothly
```

**Test 2: Visitor History with 5000+ Records**
```php
// Create test data with stored procedure or direct INSERT
INSERT INTO visiteurs (nom, prenom, email, ...) VALUES (...) -- 5000 times

// Then:
// 1. Navigate to /visitor/history
// 2. Check page load time (should be < 2s)
// 3. Verify date filter reduces records
```

**Test 3: Realtime API Load**
```bash
# Simulate 100 concurrent API calls
ab -n 100 -c 10 http://localhost:8080/api/presences/today

# Expected: < 500ms response time each
```

---

## Database Backup Before Testing

**Backup your database before running CRON commands**:
```bash
# Windows
mysqldump -u root -p ocanada_db > backup_before_phase2_test.sql

# Linux
mysqldump -u root -pocanada_db > backup_before_phase2_test.sql

# To restore if needed:
mysql -u root -p ocanada_db < backup_before_phase2_test.sql
```

---

## Next Steps After Phase 2 Testing

Once all tests pass:

1. **Phase 3 - Leave Management**: 
   - Implement leave request creation & approval
   - Link presence data to leave requests
   - Create WorkingDaysCalculator integration

2. **Phase 4 - Advanced Reporting**:
   - Export presence/visitor data
   - Create PDF reports
   - Build custom dashboards

3. **Phase 5 - Notifications & Integration**:
   - Email alerts for high tardiness
   - SMS reminders for visitors
   - Sync with external systems

---

## Support & Questions

For implementation details, see:
- `PHASE_2_COMPLETION_REPORT.md` - Full documentation
- `/memories/session/phase2_visitor_implementation.md` - Session notes
- `app/Controllers/VisitorController.php` - Code comments
- `app/Controllers/Admin/PresencesController.php` - Code comments
- `app/Controllers/API/RealtimeController.php` - Code comments

---

*Quick Start Guide - Phase 2*
*Last Updated: 2024*
