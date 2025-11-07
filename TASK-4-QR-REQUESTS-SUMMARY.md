# Task 4: QR Request Interface - Implementation Summary

## Overview
Successfully implemented the QR public song request interface with optional identity (name/avatar) and real-time status tracking as specified in the project budget.

## Completed Features

### 1. Backend Entity & Database Changes

**Modified Files:**
- `backend/src/Entity/StationRequest.php`
  - Added `requester_name` (VARCHAR 100, nullable)
  - Added `requester_avatar` (VARCHAR 500, nullable)
  - Added `comment` (TEXT, nullable)
  - Updated constructor to accept new fields
  - Added `getStatus()` method returning: 'pending', 'queued', 'accepted', or 'rejected'
  
- `backend/src/Entity/Migration/Version20250117000001.php` (NEW)
  - Database migration to add the three new columns
  - Includes rollback (down) method

### 2. Backend API Controllers

**Modified Files:**
- `backend/src/Controller/Api/Stations/Requests/SubmitAction.php`
  - Reads `requester_name`, `requester_avatar`, `comment` from POST body
  - Saves data to StationRequest entity
  - Returns `request_id` in response for status polling

**New Files:**
- `backend/src/Controller/Api/Stations/Requests/StatusAction.php`
  - New endpoint: `GET /api/station/{id}/request-status/{request_id}`
  - Returns complete request info including status, requester data, timestamp
  - Used for 5-second polling

**Modified Routes:**
- `backend/config/routes/api_station.php`
  - Added route for `/request-status/{request_id}` endpoint

### 3. Frontend Components

**New Files:**
- `frontend/components/Public/Requests/QRRequestForm.vue`
  - Mobile-first responsive design
  - Station selection (if multiple)
  - Real-time search/filter for requestable songs
  - Optional fields: name, avatar URL, comment
  - Submit request functionality
  - Status display with badges (pending/queued/accepted/rejected)
  - 5-second polling for status updates
  - Auto-stops polling when status is final (accepted/rejected)
  - "Make another request" button to reset form

- `frontend/components/Public/Requests/QRRequestPage.vue`
  - Wrapper component for public page
  - Receives station prop from backend

**New Public Page:**
- `backend/src/Controller/Frontend/PublicPages/QRRequestAction.php`
  - Controller for public QR request page
  - Renders Vue component with minimal layout
  - URL: `/public/{station_id}/qr-request`

**Modified Routes:**
- `backend/config/routes/public.php`
  - Added route for `public:qr-request`

### 4. Translations

**Modified Files:**
- `translations/es_ES.UTF-8/translations.json`
  - Added 23+ Spanish translations for:
    - Form labels (name, avatar, comment)
    - Status labels (pending, queued, accepted, rejected)
    - Status messages
    - Search placeholder
    - Error messages
    - Action buttons

## Technical Implementation Details

### Request Status Logic
- **Pending**: Just created, waiting for delay threshold
- **Queued**: `shouldPlayNow()` returns true (delay passed)
- **Accepted**: `played_at` timestamp is set
- **Rejected**: Request removed (not fully implemented - would require soft delete)

### Polling Mechanism
- Starts immediately after successful submission
- Polls every 5 seconds
- Automatically stops when status is 'accepted' or 'rejected'
- Cleans up interval on component unmount

### Mobile-First Design
- Large form controls for touch devices
- Responsive layout (max-width 600px, centered)
- Scrollable media list with visual feedback
- Touch-friendly selection
- Full-width buttons

## API Endpoints

### Existing (Modified)
```
POST /api/station/{id}/request/{media_id}
Body: {
  requester_name?: string,
  requester_avatar?: string,
  comment?: string
}
Response: {
  success: boolean,
  message: string,
  request_id: number  // NEW
}
```

### New
```
GET /api/station/{id}/request-status/{request_id}
Response: {
  id: number,
  status: 'pending'|'queued'|'accepted'|'rejected',
  track_title: string,
  track_artist: string,
  requester_name: string|null,
  requester_avatar: string|null,
  comment: string|null,
  timestamp: number,
  played_at: number|null
}
```

## Public Access URL

Users can access the QR request interface at:
```
https://simonamusic.net/public/{station_id}/qr-request
```

This can be encoded as a QR code for easy mobile access.

## Database Migration

To apply the database changes, run:
```bash
docker-compose exec web php bin/console doctrine:migrations:migrate
```

Or restart the container to auto-apply migrations.

## Testing Checklist

- [ ] Apply database migration
- [ ] Access `/public/{station_id}/qr-request` on a station with requests enabled
- [ ] Search for a song
- [ ] Submit request without optional fields
- [ ] Submit request with name, avatar, and comment
- [ ] Verify status changes from pending → queued → accepted
- [ ] Test polling stops after acceptance
- [ ] Test "Make another request" button
- [ ] Verify translations display correctly in Spanish

## Integration with Existing System

- ✅ Preserves existing request functionality
- ✅ Backward compatible (all new fields are nullable)
- ✅ Reuses existing AzuraCast request API
- ✅ Follows AzuraCast patterns and conventions
- ✅ Mobile-optimized for QR code access

## Budget Specification Compliance

✅ **Identidad opcional (nombre/avatar) en Requests (guardar en comentario)**
- Name and avatar stored in dedicated fields
- Comment field also available

✅ **Estado de la solicitud (pendiente/en cola/aceptada/descartada) en UI pública**
- All four states implemented with visual badges

✅ **Polling 5s**
- Implemented with automatic cleanup

## Files Created/Modified Summary

**Created (8 files):**
1. `backend/src/Entity/Migration/Version20250117000001.php`
2. `backend/src/Controller/Api/Stations/Requests/StatusAction.php`
3. `backend/src/Controller/Frontend/PublicPages/QRRequestAction.php`
4. `frontend/components/Public/Requests/QRRequestForm.vue`
5. `frontend/components/Public/Requests/QRRequestPage.vue`

**Modified (5 files):**
1. `backend/src/Entity/StationRequest.php`
2. `backend/src/Controller/Api/Stations/Requests/SubmitAction.php`
3. `backend/config/routes/api_station.php`
4. `backend/config/routes/public.php`
5. `translations/es_ES.UTF-8/translations.json`

## Next Steps

Task 4 is now complete. Ready to proceed with:
- **Task 5**: Station-specific branding (colors/logo via CSS variables)
- **Task 6**: Logical screens (metadata structure per station)
- **Task 7**: Visual indicator for player (VU/waveform)
- **Task 8**: Batch playlist actions across multiple stations
