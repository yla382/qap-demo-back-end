## Business Requirements

### Problem Statement

Quadient Accounts Payable (QAP) provides a modular platform to process invoices, requiring critical data points such as Vendor and Account details. Some QAP customers use Xero, a popular SaaS accounting solution, to manage their vendor and account data.

To streamline the invoice processing workflow, the QAP engineering team has been tasked with integrating the Xero API into the Invoice Module. This integration aims to automatically retrieve and sync Vendor and Account data from Xero, ensuring QAP users can efficiently process invoices without manual data entry.

### Objectives
- Extract all Accounts and Vendors from the sample company using Xero API
- Store extracted data to disk in QAP environment 
- Provide graphical user interfaces (GUI) for data management

### Functional Requirement
- Implement OAuth 2.0 authentication to access data in Xero securely and ensure proper storage of token and refresh token for continuous access to the data
- Store the fetched data within QAP system
- Develop a RESTful API to expose the fetched data
- Material UI components to create a responsive interface for displaying vendor and account data.

### Non-Functional Requirement
- Usability: Focuses on the ease of use and the user experience of the system
- Reliability: Ensures that the system operates with failures and can recover from issues
- Maintainability: The code should be modular and easily extensible for future feature addition.

### Deliverable
- API documentation to provide endpoints and to interact with Xero data
- Source code of both backend(PHP) and frontend
-  Instructions on deploying the solution to the production server

## System Design
### Backend
- Framework: 
	- PHP Slick
- Authentication:
	- Use OAuth 2.0 to authenticate with Xero API
	- Tokens and Refresh Tokens stored in the session
- Data handling
	- Upon successful authentication, vendors and accounts data are fetched from Xero API
	- Data is stored in JSON file for a persistent storage option
- Exposing APIs:
	- `/accounts`: Returns account data from the stored JSON file
	- `/vendors`: Returns vendor data from the stored JSON file

### Frontend
- Framework:
	- React
- Data Retrieval:
	- Calls `/accounts` and `/vendors` API once the backend is ready.
	- Parse and processes data for frontend rendering
- UI Library:
	- Use Material UI to display fetched data in data table with features such as sorting and pagination
### System Diagram
- Use [Mermaid Live Editor](https://mermaid-js.github.io/mermaid-live-editor/) to paste and visualize the diagram below.
```
graph TD
    subgraph Frontend [React Frontend]
        A1[Material UI DataTable]
        A2["/vendors API"]
        A3["/accounts API"]
        A4["Connect to Xero"]
    end

    subgraph Backend [PHP Slim Backend]
        B1[Xero Authentication via OAuth 2.0]
        B2[Session Storage - Tokens]
        B3[Fetch Data from Xero API]
        B4[Store Data in JSON Files]
        B5["/vendors Endpoint"]
        B6["/accounts Endpoint"]
    end

    subgraph External [Xero API]
        C1[Vendor Data]
        C2[Account Data]
    end

    A2 --> B5
    A3 --> B6
    B5 --> B4
    B6 --> B4
    B4 --> B3
    B3 --> C1
    B3 --> C2
    B1 --> B3
    A1 --> A2
    A1 --> A3
    A4 --> B1

```

## Project Setup Instructions

### Prerequisites
1. PHP (version 8.0 or higher)
2. Composer (dependency manager for PHP)
3. Node.js (for front-end dependencies, version 16+ recommended)
4. Git (to clone the repository)

### 1. Clone the Repository
Backend
```
git clone https://github.com/yla382/qap-demo-back-end.git
```

Frontend
```
git clone https://github.com/yla382/qap-demo-front-end.git
```

### 2. Install Dependencies
Backend
```
composer install
```

Frontend
```
npm install
```

### 3. Configure Xero API
1. Create Xero account - https://developer.xero.com/
2. Create new web app with following configurations
   - Company or application URL: https://www.xero.com
   - Redirect URIs: http://localhost:8000/xero/redirect
   - Generate secret and copy

### 4. Configure Environment Variable (Backend only)

Edit the ``.env`` file to configure the necessary environment variables such as database connection details and API keys. For example:
```
XERO_CLIENT_ID=your-client-id
XERO_CLIENT_SECRET=your-client-secret
XERO_REDIRECT_URI=http://localhost:8000/xero/redirect
XERO_AUTH_URL=https://login.xero.com/identity/connect/authorize
XERO_TOKEN_URL=https://identity.xero.com/connect/token
XERO_USER_URI=https://api.xero.com/api.xro/2.0/Users
```

Update ``php.ini`` to specify CACert
```
[curl]
; A default value for the CURLOPT_CAINFO option. This is required to be an
; absolute path.
curl.cainfo = C:\Users\yoonh\Desktop\QAP - Demo\cacert.pem
```

### 5. Run the Project Locally
Backend
```
php -S localhost:8000 -t webroot
```

Frontend
```
npm run
```

### 6. Access the Application
- Backend: http://localhost:8000
- Frontend: http://localhost:3001
