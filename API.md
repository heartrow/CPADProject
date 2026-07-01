# GreenStep API Documentation

Base URL: `https://your-railway-app.railway.app`

All protected routes require a Bearer token in the `Authorization` header:
```
Authorization: Bearer <token>
```

---

## Endpoint Summary

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | /auth/register | Public | Register a new user |
| POST | /auth/login | Public | Login and get JWT token |
| GET | /auth/me | Required | Get current user profile |
| PUT | /auth/profile | Required | Update user profile |
| GET | /api/activitylogs | Required | Get all logs for current user |
| GET | /api/activitylogs/{id} | Required | Get a single log |
| POST | /api/activitylogs | Required | Create a new log |
| PUT | /api/activitylogs/{id} | Required | Update a log |
| DELETE | /api/activitylogs/{id} | Required | Delete a log |
| GET | /api/activitytypes | Required | Get all activity types |
| GET | /api/activitytypes/{id} | Required | Get a single activity type |
| POST | /api/activitytypes | Admin only | Create an activity type |
| PUT | /api/activitytypes/{id} | Admin only | Update an activity type |
| DELETE | /api/activitytypes/{id} | Admin only | Delete an activity type |
| GET | /api/badges | Required | Get all badges for current user |
| POST | /api/badges/check | Required | Check and award eligible badges |
| POST | /api/badges | Admin only | Create a new badge |
| DELETE | /api/badges/{id} | Admin only | Delete a badge |
| GET | /api/usertemplates | Required | Get all templates for current user |
| GET | /api/usertemplates/{id} | Required | Get a single template |
| POST | /api/usertemplates | Required | Create a new template |
| PUT | /api/usertemplates/{id} | Required | Update a template |
| DELETE | /api/usertemplates/{id} | Required | Delete a template |
| GET | /api/challenges | Required | Get all challenges |
| POST | /api/challenges | Admin only | Create a new challenge |
| POST | /api/challenges/join | Required | Join a challenge |
| POST | /api/challenges/leave | Required | Leave a challenge |
| GET | /api/challenges/{id}/leaderboard | Required | Get challenge leaderboard |
| DELETE | /api/challenges/{id} | Admin only | Delete a challenge |
| GET | /api/ecotips | Required | Get all eco tips |
| GET | /api/ecotips/{id} | Required | Get a single eco tip |
| POST | /api/ecotips | Admin only | Create an eco tip |
| PUT | /api/ecotips/{id} | Admin only | Update an eco tip |
| DELETE | /api/ecotips/{id} | Admin only | Delete an eco tip |

---

## Authentication

### POST /auth/register
Register a new user account.

**Auth:** Public

**Request Body:**
```json
{
  "name": "Ahmad bin Ali",
  "email": "ahmad@example.com",
  "password": "secret123"
}
```

**Validation:**
- `name` — required, 2–150 chars
- `email` — required, valid email format
- `password` — required, min 6 chars

**Response `201 Created`:**
```json
{
  "message": "Registered",
  "user": {
    "id": 1,
    "name": "Ahmad bin Ali",
    "email": "ahmad@example.com",
    "role": "member"
  }
}
```

**Response `400 Bad Request`:**
```json
{
  "errors": {
    "email": "invalid email",
    "password": "password must be at least 6 chars"
  }
}
```

**Response `409 Conflict`:**
```json
{
  "error": "Email already registered"
}
```

---

### POST /auth/login
Log in and receive a JWT access token.

**Auth:** Public

**Request Body:**
```json
{
  "email": "ahmad@example.com",
  "password": "secret123"
}
```

**Response `200 OK`:**
```json
{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ...",
  "user": {
    "id": 1,
    "name": "Ahmad bin Ali",
    "email": "ahmad@example.com",
    "role": "member"
  }
}
```

**Response `401 Unauthorized`:**
```json
{
  "error": "Invalid credentials"
}
```

---

### GET /auth/me
Get the currently authenticated user's profile.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "id": 1,
  "name": "Ahmad bin Ali",
  "email": "ahmad@example.com",
  "role": "member"
}
```

---

### PUT /auth/profile
Update the authenticated user's profile.

**Auth:** Required

**Request Body** (all fields optional):
```json
{
  "name": "Ahmad bin Ali Updated",
  "email": "newemail@example.com",
  "password": "newpassword123"
}
```

**Validation:**
- `name` — optional, 2–150 chars
- `email` — optional, valid email format
- `password` — optional, min 6 chars

**Response `200 OK`:**
```json
{
  "message": "Profile updated",
  "user": {
    "id": 1,
    "name": "Ahmad bin Ali Updated",
    "email": "newemail@example.com",
    "role": "member"
  }
}
```

---

## Activity Logs

### GET /api/activitylogs
Get all activity logs for the authenticated user.

**Auth:** Required

**Query Parameters:**
| Parameter | Type | Description |
|---|---|---|
| `limit` | integer | Max number of logs to return (optional) |

**Response `200 OK`:**
```json
{
  "count": 2,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "activity_type_id": 3,
      "title": "Going to class",
      "amount": 5.00,
      "co2_emission": 1.0500,
      "created_at": "2026-06-28 14:44:37",
      "updated_at": "2026-06-28 14:44:37",
      "activity_name": "Private Car (Petrol)",
      "category": "transport",
      "unit": "km",
      "co2_per_unit": 0.2100
    }
  ]
}
```

---

### GET /api/activitylogs/{id}
Get a single activity log by ID.

**Auth:** Required (must own the log)

**Response `200 OK`:**
```json
{
  "id": 1,
  "user_id": 1,
  "activity_type_id": 3,
  "title": "Going to class",
  "amount": 5.00,
  "co2_emission": 1.0500,
  "created_at": "2026-06-28 14:44:37",
  "updated_at": "2026-06-28 14:44:37"
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Log not found or unauthorized"
}
```

---

### POST /api/activitylogs
Create a new activity log.

**Auth:** Required

**Request Body:**
```json
{
  "activity_type_id": 3,
  "title": "Going to class",
  "amount": 5.00
}
```

**Validation:**
- `activity_type_id` — required, positive integer
- `title` — required, 1–200 chars
- `amount` — required, positive number

**Response `201 Created`:**
```json
{
  "message": "Activity logged",
  "data": {
    "id": 1,
    "user_id": 1,
    "activity_type_id": 3,
    "title": "Going to class",
    "amount": 5.00,
    "co2_emission": 1.0500,
    "created_at": "2026-06-28 14:44:37",
    "updated_at": "2026-06-28 14:44:37"
  }
}
```

---

### PUT /api/activitylogs/{id}
Update an existing activity log.

**Auth:** Required (must own the log)

**Request Body** (all fields optional):
```json
{
  "activity_type_id": 4,
  "title": "Updated title",
  "amount": 10.00
}
```

**Response `200 OK`:**
```json
{
  "message": "Activity updated",
  "data": {
    "id": 1,
    "title": "Updated title",
    "amount": 10.00,
    "co2_emission": 2.1000
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Activity log 1 not found"
}
```

---

### DELETE /api/activitylogs/{id}
Delete an activity log.

**Auth:** Required (must own the log)

**Response `200 OK`:**
```json
{
  "message": "Activity log deleted",
  "data": {
    "id": 1,
    "title": "Going to class"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Activity log 1 not found"
}
```

---

## Activity Types

### GET /api/activitytypes
Get all activity types. Supports filtering by name or category.

**Auth:** Required

**Query Parameters:**
| Parameter | Type | Description |
|---|---|---|
| `q` | string | Search by name or category (e.g. `meal`, `transport`) |
| `limit` | integer | Max number of results (optional) |

**Response `200 OK`:**
```json
{
  "count": 4,
  "data": [
    {
      "id": 1,
      "category": "transport",
      "name": "Private Car (Petrol)",
      "unit": "km",
      "co2_per_unit": 0.2100
    }
  ]
}
```

---

### GET /api/activitytypes/{id}
Get a single activity type by ID.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "id": 1,
  "category": "transport",
  "name": "Private Car (Petrol)",
  "unit": "km",
  "co2_per_unit": 0.2100
}
```

**Response `404 Not Found`:**
```json
{
  "error": "not found"
}
```

---

### POST /api/activitytypes
Create a new activity type.

**Auth:** Required (admin only)

**Request Body:**
```json
{
  "category": "transport",
  "name": "Motorcycle (Petrol)",
  "unit": "km",
  "co2_per_unit": 0.1100
}
```

**Validation:**
- `category` — required, one of: `transport`, `meal`, `energy`, `recycle`
- `name` — required, 1–150 chars
- `unit` — required, 1–50 chars
- `co2_per_unit` — required, non-negative number

**Response `201 Created`:**
```json
{
  "message": "Activity Type created",
  "data": {
    "id": 5,
    "category": "transport",
    "name": "Motorcycle (Petrol)",
    "unit": "km",
    "co2_per_unit": 0.1100
  }
}
```

**Response `403 Forbidden`:**
```json
{
  "error": "Admins only"
}
```

---

### PUT /api/activitytypes/{id}
Update an existing activity type.

**Auth:** Required (admin only)

**Request Body** (all fields optional):
```json
{
  "co2_per_unit": 0.1200
}
```

**Response `200 OK`:**
```json
{
  "message": "Activity type updated",
  "data": {
    "id": 5,
    "category": "transport",
    "name": "Motorcycle (Petrol)",
    "unit": "km",
    "co2_per_unit": 0.1200
  }
}
```

---

### DELETE /api/activitytypes/{id}
Delete an activity type.

**Auth:** Required (admin only)

**Response `200 OK`:**
```json
{
  "message": "Activity type deleted",
  "data": {
    "id": 5,
    "name": "Motorcycle (Petrol)"
  }
}
```

---

## Badges

### GET /api/badges
Get all badges, showing which ones the authenticated user has earned.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "count": 3,
  "data": [
    {
      "badge_id": 1,
      "name": "First Step",
      "description": "Log 1 eco-activity.",
      "criteria_type": "total_logs",
      "threshold": 1,
      "unlocked": true,
      "earned_at": "2026-06-28 10:00:00"
    },
    {
      "badge_id": 2,
      "name": "Carbon Saver",
      "description": "Save a total of 10 kg of CO₂ through your activities.",
      "criteria_type": "total_co2_saved_kg",
      "threshold": 10,
      "unlocked": false,
      "earned_at": null
    }
  ]
}
```

---

### POST /api/badges/check
Check if the authenticated user has met the criteria for any unearned badges and award them automatically.

**Auth:** Required

**Request Body:** None required

**Response `200 OK`:**
```json
{
  "awarded": [
    {
      "badge_id": 1,
      "name": "First Step",
      "description": "Log 1 eco-activity."
    }
  ]
}
```

---

### POST /api/badges
Create a new global badge.

**Auth:** Required (admin only)

**Request Body:**
```json
{
  "name": "Solar Pioneer",
  "criteria_type": "total_logs",
  "threshold": 25
}
```

For streak-based badges:
```json
{
  "name": "Transport Warrior",
  "criteria_type": "activity_category_streak",
  "days": 7,
  "category": "transport",
  "activity_type_id": 3
}
```

**Validation:**
- `name` — required, 1–150 chars
- `criteria_type` — required, one of: `total_logs`, `total_co2_saved_kg`, `activity_category_streak`
- `threshold` — required for `total_logs` and `total_co2_saved_kg`, positive integer
- `days` — required for `activity_category_streak`, positive integer
- `category` — required for `activity_category_streak`
- `activity_type_id` — required for `activity_category_streak`

**Response `201 Created`:**
```json
{
  "message": "Badge created",
  "data": {
    "badge_id": 3,
    "name": "Solar Pioneer",
    "description": "Log 25 eco-activity.",
    "criteria_type": "total_logs",
    "threshold": 25
  }
}
```

**Response `403 Forbidden`:**
```json
{
  "error": "Admins only"
}
```

---

### DELETE /api/badges/{id}
Delete a badge.

**Auth:** Required (admin only)

**Response `200 OK`:**
```json
{
  "message": "Badge deleted",
  "data": {
    "id": 3,
    "name": "Solar Pioneer"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Badge not found"
}
```

---

## User Templates

### GET /api/usertemplates
Get all templates for the authenticated user.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "count": 2,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "activity_type_id": 3,
      "title": "Commute to Campus",
      "description": "Daily drive to UTM",
      "amount": 15.00,
      "co2_emission": 3.1500,
      "created_at": "2026-06-28 14:44:37",
      "updated_at": "2026-06-28 14:44:37",
      "activity_name": "Private Car (Petrol)",
      "category": "transport",
      "unit": "km",
      "co2_per_unit": 0.2100
    }
  ]
}
```

---

### GET /api/usertemplates/{id}
Get a single template by ID.

**Auth:** Required (must own the template)

**Response `200 OK`:**
```json
{
  "id": 1,
  "user_id": 1,
  "activity_type_id": 3,
  "title": "Commute to Campus",
  "description": "Daily drive to UTM",
  "amount": 15.00,
  "co2_emission": 3.1500
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Template not found or unauthorized"
}
```

---

### POST /api/usertemplates
Create a new template.

**Auth:** Required

**Request Body:**
```json
{
  "activity_type_id": 3,
  "title": "Commute to Campus",
  "description": "Daily drive to UTM",
  "amount": 15.00
}
```

**Validation:**
- `activity_type_id` — required, positive integer
- `title` — required, 1–200 chars
- `description` — optional, max 300 chars
- `amount` — required, positive number

**Response `201 Created`:**
```json
{
  "message": "Template created",
  "data": {
    "id": 1,
    "user_id": 1,
    "activity_type_id": 3,
    "title": "Commute to Campus",
    "description": "Daily drive to UTM",
    "amount": 15.00,
    "co2_emission": 3.1500
  }
}
```

---

### PUT /api/usertemplates/{id}
Update an existing template.

**Auth:** Required (must own the template)

**Request Body** (all fields optional):
```json
{
  "title": "Updated title",
  "amount": 20.00
}
```

**Response `200 OK`:**
```json
{
  "message": "Template updated",
  "data": {
    "id": 1,
    "title": "Updated title",
    "amount": 20.00,
    "co2_emission": 4.2000
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Template 1 not found"
}
```

---

### DELETE /api/usertemplates/{id}
Delete a template.

**Auth:** Required (must own the template)

**Response `200 OK`:**
```json
{
  "message": "Template deleted",
  "data": {
    "id": 1,
    "title": "Commute to Campus"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Template 1 not found"
}
```

---

## Challenges

### GET /api/challenges
Get all available challenges with join status for the authenticated user.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "count": 2,
  "data": [
    {
      "id": 1,
      "title": "30-Day Green Commute",
      "description": "Use public transport or cycle every day for 30 days.",
      "target_goal": 30,
      "unit": "days",
      "joined": true,
      "created_at": "2026-06-01 00:00:00"
    }
  ]
}
```

---

### POST /api/challenges
Create a new challenge.

**Auth:** Required (admin only)

**Request Body:**
```json
{
  "title": "30-Day Green Commute",
  "description": "Use public transport or cycle every day for 30 days.",
  "target_goal": 30,
  "unit": "days"
}
```

**Validation:**
- `title` — required, 1–200 chars
- `description` — optional
- `target_goal` — required, positive number
- `unit` — required

**Response `201 Created`:**
```json
{
  "message": "Challenge created",
  "data": {
    "id": 1,
    "title": "30-Day Green Commute",
    "description": "Use public transport or cycle every day for 30 days.",
    "target_goal": 30,
    "unit": "days"
  }
}
```

---

### POST /api/challenges/join
Join a challenge.

**Auth:** Required

**Request Body:**
```json
{
  "challenge_id": 1
}
```

**Response `200 OK`:**
```json
{
  "message": "Joined challenge"
}
```

**Response `409 Conflict`:**
```json
{
  "error": "Already joined this challenge"
}
```

---

### POST /api/challenges/leave
Leave a challenge.

**Auth:** Required

**Request Body:**
```json
{
  "challenge_id": 1
}
```

**Response `200 OK`:**
```json
{
  "message": "Left challenge"
}
```

---

### GET /api/challenges/{id}/leaderboard
Get the leaderboard for a specific challenge.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "challenge_id": 1,
  "title": "30-Day Green Commute",
  "leaderboard": [
    {
      "rank": 1,
      "user_id": 3,
      "name": "Siti Aminah",
      "progress": 28
    },
    {
      "rank": 2,
      "user_id": 1,
      "name": "Ahmad bin Ali",
      "progress": 22
    }
  ]
}
```

---

### DELETE /api/challenges/{id}
Delete a challenge.

**Auth:** Required (admin only)

**Response `200 OK`:**
```json
{
  "message": "Challenge deleted",
  "data": {
    "id": 1,
    "title": "30-Day Green Commute"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Challenge not found"
}
```

---

## Eco Tips

### GET /api/ecotips
Get all eco tips.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "count": 3,
  "data": [
    {
      "id": 1,
      "title": "Switch to LED bulbs",
      "content": "LED bulbs use up to 80% less energy than traditional incandescent bulbs.",
      "category": "energy",
      "created_at": "2026-06-01 00:00:00"
    }
  ]
}
```

---

### GET /api/ecotips/{id}
Get a single eco tip by ID.

**Auth:** Required

**Response `200 OK`:**
```json
{
  "id": 1,
  "title": "Switch to LED bulbs",
  "content": "LED bulbs use up to 80% less energy than traditional incandescent bulbs.",
  "category": "energy",
  "created_at": "2026-06-01 00:00:00"
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Eco tip not found"
}
```

---

### POST /api/ecotips
Create a new eco tip.

**Auth:** Required (admin only)

**Request Body:**
```json
{
  "title": "Switch to LED bulbs",
  "content": "LED bulbs use up to 80% less energy than traditional incandescent bulbs.",
  "category": "energy"
}
```

**Validation:**
- `title` — required, 1–200 chars
- `content` — required
- `category` — required, one of: `transport`, `meal`, `energy`, `recycle`

**Response `201 Created`:**
```json
{
  "message": "Eco tip created",
  "data": {
    "id": 1,
    "title": "Switch to LED bulbs",
    "content": "LED bulbs use up to 80% less energy than traditional incandescent bulbs.",
    "category": "energy"
  }
}
```

---

### PUT /api/ecotips/{id}
Update an existing eco tip.

**Auth:** Required (admin only)

**Request Body** (all fields optional):
```json
{
  "title": "Updated tip title",
  "content": "Updated content here."
}
```

**Response `200 OK`:**
```json
{
  "message": "Eco tip updated",
  "data": {
    "id": 1,
    "title": "Updated tip title",
    "content": "Updated content here.",
    "category": "energy"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Eco tip not found"
}
```

---

### DELETE /api/ecotips/{id}
Delete an eco tip.

**Auth:** Required (admin only)

**Response `200 OK`:**
```json
{
  "message": "Eco tip deleted",
  "data": {
    "id": 1,
    "title": "Switch to LED bulbs"
  }
}
```

**Response `404 Not Found`:**
```json
{
  "error": "Eco tip not found"
}
```

---

## Error Responses

All endpoints may return the following common errors:

| Status | Meaning |
|---|---|
| `400` | Validation failed — check `errors` object |
| `401` | Missing or invalid token |
| `403` | Forbidden — insufficient permissions |
| `404` | Resource not found |
| `409` | Conflict — e.g. email already exists or already joined |
| `429` | Too Many Requests — rate limit exceeded |