# Week 2 Phase 3 (Planning & Shifts) - Codebase Audit
## Date: March 12, 2026
---

## Executive Summary

**Phase 3 Week 2** requires implementing Planning & Shifts functionality. The codebase has **PARTIAL completion**:
- ✅ **IMPLEMENTED**: Database migrations, `AffectationShiftModel`, integration in `PresenceCalculator`
- ❌ **NOT IMPLEMENTED**: `ShiftModel`, Planning controllers and views
- ⚠️ **INCOMPLETELY DOCUMENTED**: Routes for planning endpoints

---

## 1. MIGRATION FILES FOR SHIFTS

### Status: ✅ IMPLEMENTED (2 migration files)

#### A. `003_create_presences_and_shifts.php`
- **Location**: [app/Database/Migrations/003_create_presences_and_shifts.php](app/Database/Migrations/003_create_presences_and_shifts.php)
- **Tables Created**: 
  - `shifts_modeles` (shift definitions)
  - `affectations_shifts` (employee shift assignments)
- **Status**: Legacy migration (likely from Phase 1)
- **Tables**:
  - `shifts_modeles`: id, nom, heure_debut, heure_fin, pause_minutes, jours_actifs, actif, date_creation, date_modification
  - `affectations_shifts`: id, employe_id (FK), shift_id (FK), date_debut, date_fin, actif

#### B. `2026-03-09-000008_CreateShiftsTables.php` (NEW)
- **Location**: [app/Database/Migrations/2026-03-09-000008_CreateShiftsTables.php](app/Database/Migrations/2026-03-09-000008_CreateShiftsTables.php)
- **Status**: Recent migration (Phase 3 implementation)
- **Identical table structure** to 003_create_presences_and_shifts.php
- ⚠️ **ISSUE**: Potential **double migration** - both files create the same tables. The older 003 migration should likely be removed or marked as superseded.

---

## 2. MODELS: ShiftModel & AffectationShiftModel

### Status: ✅ PARTIALLY IMPLEMENTED

#### A. `AffectationShiftModel` ✅
- **Location**: [app/Models/AffectationShiftModel.php](app/Models/AffectationShiftModel.php)
- **Status**: FULLY IMPLEMENTED
- **Table**: `affectations_shifts`
- **Key Methods**:
  - `getShiftForEmployeeOnDate($employeId, $date)` ✅ — Returns shift for employee on given date
  - `getActiveAffectationsForEmployee($employeId)` ✅ — Returns all active assignments for employee
  - `assignShift($employeId, $shiftId, $dateDebut, $dateFin = null)` ✅ — Creates new assignment, deactivates overlapping ones
- **Validations**: Enforced on employe_id, shift_id, date_debut, date_fin
- **Timestamps**: Uses `date_creation` and `date_modification`

#### B. `ShiftModel` ❌
- **Status**: NOT IMPLEMENTED
- **Expected Location**: Should be at `app/Models/ShiftModel.php`
- **Required Methods** (per SFD):
  - `getActifs()` — Get all active shift models
  - CRUD operations for shift template management
  - Methods to query shifts_modeles table
- **Note**: The `shifts_modeles` table exists in migrations, but no Model class exists to manage it.

---

## 3. PLANNING-RELATED CONTROLLERS

### Status: ❌ NOT IMPLEMENTED

#### A. Admin Planning Controller ❌
- **Expected Location**: `app/Controllers/Admin/PlanningController.php`
- **Status**: Does not exist
- **Required Functionality** (per plan-developpement-ocanada.md, line 148):
  - Weekly shift calendar display
  - JSON endpoint for calendar data
  - Real attendance integration display
  - Shift template CRUD (create/edit/delete)
  - Employee shift assignment management

#### B. Employee Planning Controller ❌
- **Expected Location**: `app/Controllers/Employe/PlanningController.php`
- **Status**: Does not exist
- **Required Functionality** (per plan-developpement-ocanada.md, line 148):
  - Read-only 2-week personal schedule view
  - Display assigned shifts

#### C. Existing Controllers
- **Admin**: DashboardController, EmployeesController, LeaveController, PresencesController (4 controllers)
- **Employe**: DashboardController, LeaveController (2 controllers)
- **Agent**: Not yet implemented
- **Route Definition**: [app/Config/Routes.php](app/Config/Routes.php) — Planning routes are **NOT defined** in routes file

---

## 4. PresenceCalculator Integration with Shifts

### Status: ✅ INTEGRATED

#### Integration Details
- **Location**: [app/Libraries/PresenceCalculator.php](app/Libraries/PresenceCalculator.php)
- **Dependencies**: Uses `AffectationShiftModel` to fetch shifts
- **Key Methods**:

##### `calculateStatus($presence, $shift = null)` ✅
- Determines if presence is "present", "retard", or "absent"
- **Shift integration**: 
  - If no shift provided, calls `getShiftForEmployee()` to fetch it
  - If shift not found, falls back to config default times (`heure_debut_pointage_arrivee`, `heure_fin_pointage_arrivee`)
  - Uses `shift['heure_debut']` and `heure_fin` (+2h tolerance) for threshold comparison
- **Logic**:
  ```
  IF heure_pointage <= heure_debut THEN status = "present"
  ELSE IF heure_pointage <= heure_debut + 2h THEN status = "retard"
  ELSE status = "absent"
  ```

##### `calculateRetardMinutes($presence, $shift = null)` ✅
- Returns minutes late as `(heure_pointage - shift['heure_debut']) / 60`
- Returns 0 if no shift or on-time

##### `getShiftForEmployee($employeId, $date)` ✅
- Protected method that calls `AffectationShiftModel::getShiftForEmployeeOnDate()`
- Returns shift array or null

##### `markAbsencesForDate($date)` ⚠️
- Defined but **NOT IMPLEMENTED** (method stub only)
- Should mark as absent all employees who didn't clock in

#### Usage
- **KiosqueController** uses PresenceCalculator to compute status on clock-in/out
- Shift lookup is transparent to caller
- Current implementation is **functional for basic shift logic**

---

## 5. VIEWS FOR PLANNING

### Status: ❌ NOT IMPLEMENTED

- **Admin Planning Views**: ❌
  - No `app/Views/admin/planning/` folder
  - Missing: index.php (calendar), shifts.php (CRUD modals), etc.
  
- **Employee Planning Views**: ❌
  - No `app/Views/employe/planning/` folder
  - Missing: index.php (read-only schedule)

---

## 6. WHAT IS STILL NEEDED FOR FULL WEEK 2 IMPLEMENTATION

### High Priority (Core Functionality)

1. **Create `ShiftModel`** ✅
   - Model for `shifts_modeles` table
   - Methods: `getActifs()`, CRUD operations, validation
   - Location: `app/Models/ShiftModel.php`

2. **Implement `Admin/PlanningController`** ✅
   - Index action: Calendar + shift assignment form
   - JSON endpoint for calendar data
   - CRUD actions for shift templates
   - Assignment actions (assign, unassign, edit)
   - Validation and error handling
   - Location: `app/Controllers/Admin/PlanningController.php`

3. **Implement `Employe/PlanningController`** ✅
   - Index action: Read-only 2-week schedule
   - Display current and future assignments
   - Location: `app/Controllers/Employe/PlanningController.php`

4. **Create Planning Views** ✅
   - `app/Views/admin/planning/index.php` — Weekly calendar + assignment UI
   - `app/Views/admin/planning/shifts.php` — Shift CRUD modals
   - `app/Views/employe/planning/index.php` — 2-week read-only schedule

5. **Update Routes** ✅
   - Add Admin/Planning routes: GET `/admin/planning`, POST for CRUD
   - Add Employee/Planning route: GET `/employe/planning`

### Medium Priority (Testing & Documentation)

6. **Complete testing cases** (from plan-developpement-ocanada.md, line 151):
   - Shift assignment overlap scenarios
   - Calendar display with real presences
   - Weekend/holiday exclusion
   - Shift change impact on late detection

7. **Validate PresenceCalculator** with actual shift data
   - Unit tests for status calculation with shifts
   - Edge cases (shift crossing midnight, DST, etc.)

### Lower Priority

8. **Resolve Migration Conflict**
   - Decide if 003 or 2026-03-09-000008 migration should be the canonical one
   - Consider consolidating or removing duplicate

---

## 7. INTEGRATION POINTS & DEPENDENCIES

```
AffectationShiftModel
    ├─ Used by: PresenceCalculator
    ├─ Used by: PlanningController (when created)
    └─ Foreign keys: employes.id, shifts_modeles.id

ShiftModel (NEEDS CREATION)
    ├─ Should be used by: PlanningController
    ├─ Should be used by: Views for shift dropdown/list
    └─ Reads: shifts_modeles table

PresenceCalculator
    ├─ Uses: AffectationShiftModel
    ├─ Uses: ConfigSystemeModel (fallback times)
    └─ Called by: KiosqueController

Database Schema
    ├─ shifts_modeles: Defined, seeded with InitialDataSeeder
    ├─ affectations_shifts: Defined, empty initially
    └─ presences: Links to shifts_modeles via shift_modele_id (FK)
```

---

## 8. CURRENT DATABASE SEEDING

**File**: [app/Database/Seeds/InitialDataSeeder.php](app/Database/Seeds/InitialDataSeeder.php)

- **Status**: Shifts are pre-seeded with default shift models
- **Example data**: May include standard shifts (e.g., Morning 08:00-17:00, Afternoon 10:00-19:00)
- **Affectations**: Likely empty on initial seed

---

## 9. COMPLIANCE WITH DEVELOPMENT PLAN

### Plan Requirements (Phase 3, Week 2)
From [plan-developpement-ocanada.md](plan-developpement-ocanada.md) lines 146-150:

| Requirement | Status | Location |
|---|---|---|
| Implement `ShiftModel`, `AffectationShiftModel` | ⚠️ 50% (Model only) | AffectationShiftModel.php ✅, ShiftModel.php ❌ |
| Create/manage shifts & affectations screens | ❌ Not started | Needs PlanningController + Views |
| Implement `Admin/PlanningController` | ❌ Not started | Needs creation |
| Weekly calendar, JSON data, real presences | ❌ Not started | Needs controller + views |
| Implement `employe/planning` | ❌ Not started | Needs controller + views |
| Integrate shifts into `PresenceCalculator` | ✅ Complete | PresenceCalculator.php #calculateStatus() |
| Integrate shifts into views | ⚠️ Partial | Visible in PresencesController but not planning views |

---

## 10. SUMMARY TABLE

| Component | File | Status | Notes |
|---|---|---|---|
| **Migrations** | 003_create_presences_and_shifts.php | ✅ Created | Duplicate warning |
| | 2026-03-09-000008_CreateShiftsTables.php | ✅ Created | |
| **Models** | AffectationShiftModel.php | ✅ Complete | All methods implemented |
| | ShiftModel.php | ❌ Missing | Needs CRUD + getActifs() |
| **Controllers** | Admin/PlanningController.php | ❌ Missing | High priority |
| | Employe/PlanningController.php | ❌ Missing | High priority |
| **Library** | PresenceCalculator.php | ✅ Integrated | Shift support complete |
| **Views** | admin/planning/* | ❌ Missing | 2-3 view files needed |
| | employe/planning/* | ❌ Missing | 1 view file needed |
| **Routes** | Routes.php | ⚠️ Incomplete | Planning routes not defined |
| **Tests** | Planning tests | ❌ Not started | Per plan requirements |

---

## 11. RECOMMENDED NEXT STEPS

1. **Create ShiftModel** with methods:
   - `getActifs()` → all active shifts
   - `getById($id)` → single shift
   - Full CRUD with validation

2. **Create Admin/PlanningController** with:
   - `index()` action (return calendar view + shift list)
   - `shiftCrudModal()` actions (create/edit/delete shifts)
   - `assignShift()` action (POST for assignments)
   - `getScheduleJson()` action (for calendar AJAX)

3. **Create views**:
   - Calendar UI (using Chart.js or similar)
   - Shift assignment form
   - Employee selector dropdown

4. **Add routes** to Routes.php for all planning endpoints

5. **Write tests** for shift scenarios and PresenceCalculator validation

6. **Validate data flow** end-to-end (assignment → presence calculation → status display)

---

## CONCLUSION

**Week 2 Phase 3 (Planning & Shifts) is approximately 40% complete:**
- Database and core model for affectations: ✅ Ready
- Shift model and controllers: ❌ Not started
- PresenceCalculator integration: ✅ Complete
- Planning UI and views: ❌ Not started

**Estimated effort to completion**: 5-7 days for full implementation including testing.

**Critical path**: ShiftModel → PlanningController → Views → Routes → Testing

---

*This audit was performed on March 12, 2026.*
*Workspace: c:\ocanada*
