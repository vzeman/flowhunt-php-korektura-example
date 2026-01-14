# FlowHunt PHP SDK Examples

This repository contains PHP examples for invoking FlowHunt flows using the official FlowHunt PHP SDK.

## Features

- ✅ Two API approaches: Direct invocation & Session-based (recommended)
- ✅ Invoke FlowHunt flows programmatically
- ✅ Poll for task completion status (5s initial wait, then every 5s, max 60 attempts)
- ✅ Retrieve and display output data
- ✅ Handle errors gracefully
- ✅ Slovak text input support
- ✅ Production-ready OOP implementation

## Requirements

- PHP 8.1 or higher
- Composer
- FlowHunt account with API access

## Installation

1. Clone this repository or copy the files to your project directory.

2. Install dependencies using Composer:

```bash
composer install
```

3. Copy the `.env.example` file to `.env` and configure your credentials:

```bash
cp .env.example .env
```

4. Edit `.env` and add your FlowHunt credentials:

```env
FLOWHUNT_API_KEY=your-api-key-here
FLOWHUNT_WORKSPACE_ID=your-workspace-id-here
FLOWHUNT_FLOW_ID=your-flow-id-here
```

## Usage

### Basic Example - Article Analysis

This repository contains a single example file that demonstrates real-world usage:

```bash
php basic-invoke.php
```

**What it does:**

This example processes a Slovak political news article through FlowHunt's AI flow. The article discusses Czech politician Filip Turek and his controversial appointment process. The script:

1. **Loads a Slovak article** - Contains approximately 3,000 characters of political commentary about Filip Turek's role in the Czech government and President Pavel's opposition to his ministerial appointment
2. **Creates a FlowHunt session** - Initializes a session from your configured flow
3. **Sends the article for processing** - Invokes the flow with the complete article text as input
4. **Polls for AI responses** - Uses timestamp-based polling to collect AI-generated analysis or responses about the article
5. **Displays the results** - Shows all AI messages generated during the flow execution

**Use Case:**

This demonstrates how FlowHunt can be used for content analysis tasks like:
- Article summarization
- Political commentary analysis
- Extracting key points from long-form content
- Generating insights from Slovak-language text

**Technical Features:**
- Session-based approach (recommended for production)
- Three-step workflow: Create session → Invoke → Poll response
- Timestamp-based polling (prevents duplicate responses)
- Handles Slovak text encoding properly
- **Automatic korektura file detection**: Monitors event stream for `artefacts` action type
- **Smart file download**: Automatically constructs S3 URL and downloads the korektura JSON
- **Early termination**: Stops polling immediately once korektura file is found and processed
- Robust error handling with retries
- Polls every 5 seconds, up to 60 attempts (5 minutes max)
- Automatic detection of flow completion or failure

## API Approaches

### Approach 1: Session-Based (Recommended for Production)

Uses three API endpoints for a complete workflow:

```php
// 1. Create session from flow
$sessionRequest = new FlowSessionCreateRequest([
    'workspace_id' => $workspaceId
]);
$session = $apiInstance->createFlowSession($flowId, $sessionRequest);
$sessionId = $session->getId();

// 2. Invoke session
$invokeRequest = new FlowInvokeRequest([
    'human_input' => $slovakText
]);
$apiInstance->invokeFlowResponse($sessionId, $invokeRequest);

// 3. Poll for response with timestamp
$fromTimestamp = 0;
$response = $apiInstance->pollFlowResponse($sessionId, $fromTimestamp);
```

**Endpoints:**
- `POST /v2/flows/sessions/from_flow/create`
- `POST /v2/flows/sessions/{session_id}/invoke`
- `POST /v2/flows/sessions/{session_id}/invocation_response/{from_timestamp}`

**Advantages:**
- ✅ More robust for production
- ✅ Better error handling
- ✅ Timestamp-based polling
- ✅ No duplicate responses
- ✅ Better for long-running flows

### Approach 2: Direct Invocation (Simpler)

Direct flow invocation with task polling:

```php
// Invoke flow directly
$invokeRequest = new FlowInvokeRequest([
    'human_input' => $slovakText
]);
$result = $apiInstance->invokeFlow($flowId, $workspaceId, $invokeRequest);
$taskId = $result->getId();

// Poll for results
$taskResult = $apiInstance->getInvokedFlowResults($flowId, $taskId, $workspaceId);
```

**Endpoints:**
- `POST /v2/flows/{flow_id}/invoke?workspace_id={workspace_id}`
- `GET /v2/flows/{flow_id}/{task_id}?workspace_id={workspace_id}`

**Advantages:**
- ✅ Simpler code
- ✅ Fewer API calls
- ✅ Good for testing
- ✅ Quick to implement

## Code Structure

The `basic-invoke.php` file demonstrates the complete session-based workflow:

### 1. Setup and Configuration

```php
// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configure API client
$config = Configuration::getDefaultConfiguration()
    ->setHost('https://api.flowhunt.io')
    ->setApiKey('Api-Key', $apiKey);

$apiInstance = new FlowsApi(new Client(), $config);
```

### 2. Create Session from Flow

```php
$sessionRequest = new FlowSessionCreateFromFlowRequest([
    'flow_id' => $flowId
]);

$session = $apiInstance->createFlowSession($workspaceId, $sessionRequest);
$sessionId = $session->getSessionId();
```

### 3. Invoke with Article Content

```php
$invokeRequest = new FlowSessionInvokeRequest([
    'message' => $articleText  // The Slovak article
]);

$apiInstance->invokeFlowResponse($sessionId, $invokeRequest);
```

### 4. Poll for AI Responses and Detect Korektura Artifact

```php
$fromTimestamp = 0;
$aiMessages = [];
$korekturaFileUrl = null;

while ($attempt < $maxAttempts && !$korekturaFileUrl) {
    $events = $apiInstance->pollFlowResponse($sessionId, $fromTimestamp);

    foreach ($events as $event) {
        $actionType = $event->getActionType();

        // Update timestamp for next poll
        if ($event->getCreatedAtTimestamp()) {
            $fromTimestamp = $event->getCreatedAtTimestamp();
        }

        // Collect AI messages
        if ($event->getEventType() == 'ai' && $actionType == 'message') {
            $aiMessages[] = $event->getMetadata()->getMessage();
        }

        // Check for korektura artifact
        if ($actionType === 'artefacts') {
            $metadata = $event->getMetadata();
            $artefacts = $metadata->getArtefacts();

            foreach ($artefacts as $artefact) {
                $fileName = $artefact->getName();

                if (stripos($fileName, 'korektura') !== false) {
                    // Construct download URL
                    $korekturaFileUrl = "https://urlslab-delivery.s3.eu-central-1.amazonaws.com/flow_attachments/$workspaceId/$flowId/$sessionId/$fileName";
                    break 2; // Exit both loops
                }
            }
        }

        // Check for failures
        if ($actionType == 'failed') {
            break;
        }
    }

    sleep(5);  // Wait before next poll
}
```

### 5. Download and Display Korektura Results

```php
if ($korekturaFileUrl) {
    // Download the file
    $client = new Client();
    $response = $client->request('GET', $korekturaFileUrl);
    $fileContent = $response->getBody()->getContents();

    // Parse and display JSON
    $jsonData = json_decode($fileContent, true);
    echo json_encode($jsonData,
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );
} else {
    // Display AI messages if no korektura file found
    foreach ($aiMessages as $message) {
        echo $message . "\n";
    }
}
```

## Configuration

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `FLOWHUNT_API_KEY` | Your FlowHunt API key | `1ad9cfe1-1843-4779-89a6-586339595099` |
| `FLOWHUNT_WORKSPACE_ID` | Your workspace ID | `4cb8b524-2903-4908-8e7b-ade8292b98ff` |
| `FLOWHUNT_FLOW_ID` | The flow ID to invoke | `4ad5def1-d3a3-487b-b1b6-40d12fc2bff8` |

### Polling Configuration

You can customize the polling behavior in the advanced example:

```php
$executor
    ->setPollInterval(5)      // How often to check (seconds)
    ->setMaxPollingTime(300); // Maximum wait time (seconds)
```

## Flow Inputs

The example sends a Slovak news article as the input message. The article is embedded in the code using a heredoc:

```php
$articleText = <<<'EOT'
Veľmi viditeľný Filip Turek - Ani morálne rozhorčený český prezident...
[~3000 characters of Slovak political commentary]
EOT;

$invokeRequest = new FlowSessionInvokeRequest([
    'message' => $articleText
]);
```

To process your own content, simply replace the `$articleText` variable with your text. The example supports:
- Slovak language text (UTF-8 encoded)
- Long-form content (thousands of characters)
- Multi-paragraph articles
- Any text content your FlowHunt flow is configured to process

## Output Data

The example monitors for a korektura file during flow execution. When the file is detected, it automatically downloads and displays it:

Example output:

```
=== FlowHunt Session-Based Flow Invocation ===

Step 1: Creating session from flow...
Flow ID: 4ad5def1-d3a3-487b-b1b6-40d12fc2bff8
Workspace ID: 4cb8b524-2903-4908-8e7b-ade8292b98ff
Input text length: 5693 characters

✓ Session created successfully!
Session ID: e81f21f1-3581-4666-82df-64092341e3b0

Step 2: Invoking session with Slovak text...
✓ Session invoked successfully!

Step 3: Polling for response...
Waiting 5 seconds before first check...
Attempt 1/60 - Got 6 events
Attempt 2/60 - Got 9 events
Attempt 3/60 - Got 16 events
...
Attempt 28/60 - Got 2 events

✓ Found korektura file: korektura.json

=== DOWNLOADING KOREKTURA FILE ===
File: korektura.json
URL: https://urlslab-delivery.s3.eu-central-1.amazonaws.com/flow_attachments/4cb8b524-2903-4908-8e7b-ade8292b98ff/4ad5def1-d3a3-487b-b1b6-40d12fc2bff8/e81f21f1-3581-4666-82df-64092341e3b0/korektura.json

=== KOREKTURA RESULTS ===

{
  "changes": [
    {
      "id": "change_1",
      "original": "Filip Turek zo strany Motoristi sebe...",
      "suggestions": [
        {
          "text": "Filip Turek zo strany Motoristi sebe...",
          "reason": "Filip Turek nie je čestným predsedom...",
          "confidence": 0.95
        }
      ],
      "type": "fact"
    },
    {
      "id": "change_2",
      "original": "Darmo ho Andrej Babiš presviedčal...",
      "suggestions": [
        {
          "text": "Marne ho Andrej Babiš presviedčal...",
          "reason": "'Marne' je správnejšie ako 'Darmo'...",
          "confidence": 0.85
        }
      ],
      "type": "spelling"
    }
    // ... more corrections
  ]
}

✓ Korektura file processed successfully!
```

### Korektura File Detection

The script implements intelligent artifact detection:

#### How It Works

1. **Event Stream Monitoring**: While polling for responses, the script examines each event's `action_type`
2. **Artifact Detection**: When it finds an event with `action_type === 'artefacts'`, it inspects the metadata
3. **File Identification**: Checks each artifact's name for the string "korektura" (case-insensitive)
4. **URL Construction**: Builds the download URL using the pattern:
   ```
   https://urlslab-delivery.s3.eu-central-1.amazonaws.com/flow_attachments/
   {workspace_id}/{flow_id}/{session_id}/{filename}
   ```
5. **Automatic Download**: Fetches the file via HTTP GET request using Guzzle HTTP client
6. **JSON Processing**: Parses and pretty-prints the JSON with UTF-8 encoding
7. **Early Exit**: Stops polling immediately after successfully processing the korektura file

#### Technical Implementation

```php
// Detect artefacts event
if ($actionType === 'artefacts') {
    $metadata = $event->getMetadata();
    $artefacts = $metadata->getArtefacts();

    foreach ($artefacts as $artefact) {
        $fileName = $artefact->getName();

        if (stripos($fileName, 'korektura') !== false) {
            // Construct S3 URL
            $url = "https://urlslab-delivery.s3.eu-central-1.amazonaws.com/flow_attachments/$workspaceId/$flowId/$sessionId/$fileName";

            // Download and display
            $content = $httpClient->get($url)->getBody();
            echo json_encode(json_decode($content), JSON_PRETTY_PRINT);

            break; // Stop polling
        }
    }
}
```

#### Benefits

- **No Manual Intervention**: Completely automated detection and download
- **Efficient**: Stops polling as soon as the file is ready
- **Reliable**: Uses FlowHunt's official artifact system
- **Language Support**: Handles Slovak and other UTF-8 encoded content properly

If no korektura file is found after all polling attempts, the script will display standard AI messages from the flow instead.

## Error Handling

Both examples include comprehensive error handling:

- **API Errors**: Catches and displays FlowHunt API errors
- **Network Errors**: Handles connection issues
- **Timeout**: Detects when tasks take too long
- **Task Failures**: Reports when flows fail with error details

## Task Status

Possible task statuses:

- `pending` - Task is queued
- `running` - Task is currently executing
- `completed` / `success` - Task finished successfully
- `failed` / `error` - Task encountered an error

## Troubleshooting

### API Key Issues

If you get authentication errors:

1. Verify your API key in `.env` is correct
2. Check that your API key has the necessary permissions
3. Ensure the workspace ID matches your API key

### Timeout Issues

If tasks timeout:

1. Increase `maxPollingTime` in the advanced example
2. Check if your flow has any long-running operations
3. Verify the flow is properly configured in FlowHunt

### Dependencies

If you encounter dependency issues:

```bash
# Remove existing vendor directory
rm -rf vendor/

# Clear Composer cache
composer clear-cache

# Reinstall dependencies
composer install
```

## SDK Documentation

For more information about the FlowHunt PHP SDK:

- **Package**: [flowhunt/flowhunt-php-sdk](https://packagist.org/packages/flowhunt/flowhunt-php-sdk)
- **GitHub**: [QualityUnit/flowhunt-php-sdk](https://github.com/QualityUnit/flowhunt-php-sdk)
- **FlowHunt**: [www.flowhunt.io](https://www.flowhunt.io/)

## License

This example code is provided as-is for demonstration purposes.

## Support

For issues with:
- **FlowHunt SDK**: Open an issue on the [SDK GitHub repository](https://github.com/QualityUnit/flowhunt-php-sdk)
- **FlowHunt Platform**: Contact FlowHunt support
- **These Examples**: Open an issue in this repository
