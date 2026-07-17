# 🏙️ CitySphere

A role-based city management web application built with **PHP** and **Oracle 11g XE**, where every database operation is handled by **PL/SQL stored procedures** — zero raw SQL in PHP.

Citizens file crime reports, property owners manage rentals, police officers review incidents, and admins control the entire system from a single dashboard.

---

## 📸 Screenshots

> Login page, dashboard, crime reports, and police queue.

---

## ✨ Features

- 🔐 **Multi-role authentication** — Admin, Police, House Owner, Citizen
- 📋 **Crime reporting** — file anonymously or with identity; police review queue
- 🏠 **Property & rental management** — buildings, units, payment tracking
- 👮 **Criminal records** — maintained by police, linked to verified reports
- 📢 **Announcements** — targeted by role (all, police, owners, etc.)
- 🧾 **Audit logging** — automatic trigger fires on every payment status change
- 🗄️ **100% PL/SQL backend** — 8 packages, 37 procedures, 1 function
- 🎨 **Responsive UI** — custom cyan theme, no CSS frameworks

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.0+ (vanilla, no framework) |
| Database | Oracle 11g Express Edition |
| DB Driver | OCI8 PHP extension |
| DB Logic | PL/SQL packages & stored procedures |
| Frontend | HTML5 + CSS3 + Vanilla JS |
| Auth | Session-based with `password_hash` (bcrypt) |

---

## 📁 Project Structure

```
citysphere_oracle/
│
├── config/
│   └── db.php                  # OCI8 connection
│
├── includes/
│   ├── auth.php                # Session helpers, guards, flash messages
│   ├── functions.php           # All PL/SQL call wrappers (no SQL here)
│   ├── header.php              # Navigation bar
│   └── footer.php
│
├── sql/
│   ├── 00_setup.sql            # Create Oracle user + grant privileges
│   ├── 01_schema.sql           # Tables, constraints, trigger
│   ├── 02_plsql.sql            # All PL/SQL packages & procedures
│   └── 03_seed.sql             # Admin user + sample areas
│
├── assets/
│   ├── css/style.css           # Cyan theme
│   └── js/app.js
│
├── public/uploads/             # Profile photo uploads
│
├── index.php                   # Landing page
├── login.php
├── signup.php
├── logout.php
├── dashboard.php
├── profile.php
├── areas.php                   # Admin only
├── admin_users.php             # Admin only
├── buildings.php               # Admin + House Owner
├── rentals.php
├── reports.php
├── police.php                  # Police + Admin
├── criminals.php               # Police + Admin (self-view for citizens)
└── announcements.php
```

---

## 🗄️ Database Schema

### Tables

| Table | Primary Key | Description |
|---|---|---|
| `users` | `nid` VARCHAR2(6) | All users; NID is a 6-digit national ID |
| `user_roles` | `(user_nid, role)` composite | Role assignments per user |
| `areas` | `area_id` NUMBER | City districts |
| `buildings` | `building_id` NUMBER | Properties owned by house owners |
| `rentals` | `rental_id` NUMBER | Unit-to-renter assignments |
| `crime_reports` | `report_id` NUMBER | Citizen-filed incidents |
| `criminal_records` | `record_id` NUMBER | Police-maintained criminal history |
| `audit_logs` | `log_id` NUMBER | Auto-populated by DB trigger |
| `announcements` | `id` NUMBER | Role-targeted announcements |

### Key Constraints

- `users.nid` — CHECK `REGEXP_LIKE(nid, '^[0-9]{6}$')`
- `user_roles.role` — CHECK `IN ('user','admin','house_owner','police')`
- `rentals.payment_status` — CHECK `IN ('pending','paid','overdue')`
- `rentals.status` — CHECK `IN ('active','ended')`
- `rentals` — UNIQUE `(building_id, unit_no, status)` prevents double-assigning active units
- `crime_reports.status` — CHECK `IN ('pending','verified','rejected','solved')`
- `announcements.target_role` — CHECK `IN ('all','user','house_owner','police','admin')`

### Trigger

```sql
-- Fires automatically on every payment_status change in rentals
trg_rental_payment_audit  →  writes to audit_logs
```

---

## 📦 PL/SQL Packages

All business logic lives in Oracle. PHP only calls procedures — it never constructs SQL.

| Package | Procedures / Functions |
|---|---|
| `pkg_auth` | `fn_has_role`, `sp_register_user`, `sp_get_user_by_email`, `sp_get_user_by_nid`, `sp_get_roles_for_user`, `sp_grant_role`, `sp_revoke_role`, `sp_update_profile` |
| `pkg_users` | `sp_get_all_users_with_roles`, `sp_get_users`, `sp_get_house_owners` |
| `pkg_city` | `sp_get_areas`, `sp_get_area_list`, `sp_add_area`, `sp_get_buildings`, `sp_get_building_list`, `sp_add_building` |
| `pkg_rentals` | `sp_assign_renter`, `sp_update_payment`, `sp_end_rental`, `sp_get_all_rentals`, `sp_get_my_rentals`, `sp_audit_pending_rentals` |
| `pkg_reports` | `sp_file_report`, `sp_get_all_reports`, `sp_get_my_reports`, `sp_get_police_queue`, `sp_get_verified_reports`, `sp_review_report` |
| `pkg_criminals` | `sp_add_criminal_record`, `sp_get_all_criminal_records`, `sp_get_citizen_records` |
| `pkg_dashboard` | `sp_get_dashboard_stats`, `sp_get_my_recent_reports` |
| `pkg_announcements` | `sp_post_announcement`, `sp_get_announcements_for_user` |

---




## 👥 Roles

| Role | Permissions |
|---|---|
| `user` | File reports, view own rentals, view own criminal records, edit profile |
| `house_owner` | All above + register buildings, assign renters, update payment status |
| `police` | All above + review reports, verify/reject/solve, add criminal records |
| `admin` | Full access — manage users, roles, areas, buildings, all data |

A single user can hold **multiple roles** simultaneously. Roles are assigned under **Admin → Users**.

---

## 🔐 Security

- Passwords hashed with `password_hash()` (bcrypt, cost 10)
- All DB operations use parameterised OCI8 binds — no SQL injection surface
- Anonymous reports: `reporter_nid` is set to `NULL` by PHP before the PL/SQL call; the officer sees the report but never the identity
- Role checks enforced in every PL/SQL procedure via `pkg_auth.fn_has_role()` — not just in PHP
- All output HTML-escaped with `htmlspecialchars()`
- Sessions regenerated on login to prevent fixation

---


OCI8 binding rule: every bind variable is copied into a local `$locals[]` array before being passed by reference — direct array-element access (`$arr[$key]`) causes `oci_bind_by_name()` to fail with "Invalid variable".

---




## 🙏 Acknowledgements

- [Oracle XE](https://www.oracle.com/database/technologies/appdev/xe.html) — free tier database
- [PHP OCI8](https://www.php.net/manual/en/book.oci8.php) — Oracle extension for PHP
- [XAMPP](https://www.apachefriends.org/) — local development stack
