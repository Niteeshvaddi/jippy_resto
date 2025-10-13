# Environment Setup for Admin Impersonation Feature

## Required Environment Variables

Add these variables to your `.env` file:

```env
# Firebase Admin SDK Configuration for Impersonation Feature
FIREBASE_PROJECT_ID=jippymart-27c08
FIREBASE_PRIVATE_KEY_ID=your_private_key_id_here
FIREBASE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nYour_Private_Key_Here\n-----END PRIVATE KEY-----\n"
FIREBASE_CLIENT_EMAIL=firebase-adminsdk-xxxxx@jippymart-27c08.iam.gserviceaccount.com
FIREBASE_CLIENT_ID=your_client_id_here
FIREBASE_CLIENT_X509_CERT_URL=https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40jippymart-27c08.iam.gserviceaccount.com

# Restaurant Panel URL for Impersonation Redirects
RESTAURANT_PANEL_URL=https://restaurant.jippymart.in

# Optional: Customize Rate Limiting (default: 10 attempts per hour)
IMPERSONATION_RATE_LIMIT=10
IMPERSONATION_RATE_WINDOW=3600

# Optional: Customize Token Expiration (default: 300 seconds = 5 minutes)
IMPERSONATION_TOKEN_EXPIRY=300
```

## How to Get Firebase Service Account Credentials

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (`jippymart-27c08`)
3. Click the gear icon → Project Settings
4. Go to "Service Accounts" tab
5. Click "Generate new private key"
6. Download the JSON file
7. Extract the values and add them to your `.env` file

## Example Service Account JSON Structure

```json
{
  "type": "service_account",
  "project_id": "jippymart-27c08",
  "private_key_id": "your_private_key_id",
  "private_key": "-----BEGIN PRIVATE KEY-----\nYour_Private_Key_Here\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@jippymart-27c08.iam.gserviceaccount.com",
  "client_id": "your_client_id",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-xxxxx%40jippymart-27c08.iam.gserviceaccount.com"
}
```

## Security Notes

- Keep your service account credentials secure
- Never commit the `.env` file to version control
- Use different service accounts for different environments
- Regularly rotate your service account keys
- Monitor Firebase usage and quotas
