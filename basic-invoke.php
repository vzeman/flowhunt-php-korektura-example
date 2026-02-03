<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use FlowHunt\Configuration;
use FlowHunt\Api\FlowsApi;
use FlowHunt\Model\FlowSessionCreateFromFlowRequest;
use FlowHunt\Model\FlowSessionInvokeRequest;
use FlowHunt\Model\FlowSessionArtefactInfo;
use GuzzleHttp\Client;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuration
$apiKey = $_ENV['FLOWHUNT_API_KEY'];
$workspaceId = $_ENV['FLOWHUNT_WORKSPACE_ID'];
$flowId = $_ENV['FLOWHUNT_FLOW_ID'];

try {
    // Configure API client
    $config = Configuration::getDefaultConfiguration()
        ->setHost('https://api.flowhunt.io')
        ->setApiKey('Api-Key', $apiKey);

    $httpClient = new Client();
    $apiInstance = new FlowsApi($httpClient, $config);

    // Article text to process (Slovak original)
    $articleText = <<<'EOT'
Veľmi viditeľný Filip Turek - Ani morálne rozhorčený český prezident ho z politiky nevytlačí

Česko má novú vládu. Babišovi sa ju podarilo poskladať, hoci to nemal jednoduché.

Asi najväčšie vášne vzbudzoval Filip Turek zo strany Motoristi sebe. Najprv mal obsadiť post ministra zahraničných vecí, to sa stretlo s pobúrením, a tak sa mal stať ministrom životného prostredia. Ale prezident Pavel odmietol čestného predsedu Motoristov Filipa Turka menovať aj za ministra životného prostredia. Podľa neho by tento človek vôbec nemal byť členom vlády.

Darmo ho Andrej Babiš presviedčal, aby dal Turkovi šancu stať sa ministrom a odpracovať si svoju minulosť aj kontroverzné výroky.

A tak sa Filip Turek stal vládnym zmocnencom pre klimatickú politiku a Green Deal. Bude mať na ministerstve životného prostredia kanceláriu ako minister a môže byť vplyvným mužom, lebo vláda vymedzuje úlohu, kompetencie aj rolu vládneho zmocnenca.

Strana zelených je aj tak totálne vydesená a zverejnila na FB aj krátku reakciu. "Trojitý facepalm. Klimatická úzkosť. Chuť štipnúť sa, či to nie je nočná mora."

Takto je z Filipa Turka obeť tvrdohlavého prezidenta.

Prezident Pavel sa aj tak môže teraz cítiť ako morálny víťaz, že on zabránil katastrofe. Pražské salóny mu tlieskajú.

Ale prezidentova tvrdohlavosť neoslnila všetkých. Šéfredaktor Echa Dalibor Balšínek napísal, že prezidentovi v tomto prestali rozumieť aj "jemu naklonení politickí komentátori, ktorí nakoniec uznávajú, že Filipa Turka do náhradnej funkcie, teda na post ministra životného prostredia, menovať mal".

Mohol to podľa Balšínka urobiť s veľkým varovaním, že na ministerský post nemá Turek dostatočnú kompetenciu ani politickú zdatnosť. To sa napokon pokojne mohlo aj ukázať.

Ale prezident zastavil jeho politickú kariéru, lebo Filip Turek sa preňho stal symbolom zla, túži po jeho skalpe, hlava-nehlava, bez hlbšieho rozmyslu, možno z tvrdohlavosti, ale celkom iste s absenciou hlbšej politickej stratégie.

Filip Turek sa s novou pozíciou zrejme uspokojil a oslávil ju štýlovo v reštaurácii spolu s uzbeckým zápasníkom MMA Makhmudom Muradovom, ktorý je spájaný s balkánskou mafiou, aj s človekom známym svojimi antisemitskými názormi.

Ale voliči Motoristov sú prezidentovým krokom urazení a ponížení.

Pritom Petr Pavel sa v tomto mohol poučiť aj v slovenskej politike a od Zuzany Čaputovej, s ktorou mal dobré vzťahy.

Vysoká funkcia človeka, dokonca aj takú tvrdú náturu, ako je Huliak, dokáže trochu meniť.

Pripomeňme, že prezidentka v októbri 2023 odmietla vymenovať za ministra životného prostredia Rudolfa Huliaka, lebo "svojimi vyjadreniami neguje dlhodobo platnú environmentálnu politiku tohto štátu a medzinárodné záväzky, ktorými je Slovenská republika viazaná".

Vyčítala mu aj to, že "verejne schvaľuje násilné vyrovnanie sa s názorovými oponentmi, najmä z radov ochrancov prírody a krajiny".

Budúci premiér Fico sa hneval, že prezidentka naťahuje vymenovanie novej vlády, Huliaka používa len ako zámienku a jej postup nemá základ ani v Ústave SR.

Napokon sa ministrom životného prostredia stal Tomáš Taraba, ktorý sa v roku 2020 dostal do parlamentu na kandidátke kotlebovcov. Nuž, ani to nebolo veľké morálne víťazstvo, ale prezidentka si mohla myslieť, že aspoň odpísala Huliaka.

Lenže Rudolf Huliak je politicky prefíkanejší, ako sa na prvý pohľad javí. Využil rôzne spory koalície a vydupal si nové ministerstvo, hoci aj také trochu fejkové.

Otázne je, či je Tomáš Taraba lepším ministrom životného prostredia, ako by bol Huliak.

No dokáže ho šikovne marketingovo využívať, otvára kadekde po dedinách či sídliskách nové športoviská a propaguje domáci cestovný ruch, na ktorý má štátne peniaze.

Vysoká funkcia môže obrúsiť aj takú tvrdú náturu, ako je Huliak. Už si dáva pozor na jazyk a už ho nevídavame ani v poľovníckej kamizole.

Minister Taraba je podnikateľsky skúsenejší a sebavedomejší, preto si trúfa aj na veci, ako je PVE Málinec-Látky či generálna oprava turbín Vodnej elektrárne Gabčíkovo, baví ho hrať sa s veľkými a drahými projektmi.

Rudolf Huliak by ako minister životného prostredia možno riešil najmä tie medvede. Človek nevie, čo je v tejto situácii lepšie.

Ale ešte jedna vec je pozoruhodná. Kedysi bolo ministerstvo životného prostredia nezaujímavým postom, o ktorý sa politické strany nebili. Ministerstvo bolo atraktívne iba pre strany so silným ekologickým programom.

Dnes sa oň bijú pravicové strany.

Filip Turek bude teda veľmi viditeľný v akejkoľvek funkcii. Aj keby mu dali na ministerstve len zametať.

Aj Filip Turek vyhlásil, že chce byť ministrom životného prostredia, aby "Green Deal v tejto republike ustupoval, aby neničil priemysel, energetiku, konkurencieschopnosť a neobmedzoval a neochudobňoval naše obyvateľstvo".

Turek bude stále veľmi viditeľným mužom. Môže za to aj jeho vizáž.

Poeticky o nej napísal Jiří Peňás: "Človek je obeťou aj výhercom svojho vzhľadu. Je jeho tvorcom aj zajatcom, či už ho využíva, alebo je ním zneužívaný. Nenápadný človek sa správa väčšinou nenápadne, nápadne vyzerajúci človek sa správa, nuž, nápadne."

Filip Turek svojim priaznivcom na FB odkázal, že mu teraz už nejde len o Green Deal, ale aj o to, aby v Česku naďalej fungovala parlamentná demokracia, a nie prezidentský systém.

A s touto agendou môže mať tiež slušný úspech.
EOT;

    echo "=== FlowHunt Session-Based Flow Invocation ===\n\n";

    // STEP 1: Create session from flow
    echo "Step 1: Creating session from flow...\n";
    echo "Flow ID: $flowId\n";
    echo "Workspace ID: $workspaceId\n";
    echo "Input text length: " . strlen($articleText) . " characters\n\n";

    $sessionRequest = new FlowSessionCreateFromFlowRequest([
        'flow_id' => $flowId
    ]);

    $session = $apiInstance->createFlowSession($workspaceId, $sessionRequest);
    $sessionId = $session->getSessionId();

    echo "✓ Session created successfully!\n";
    echo "Session ID: $sessionId\n\n";

    // STEP 2: Invoke the session
    echo "Step 2: Invoking session with Slovak text...\n";

    $invokeRequest = new FlowSessionInvokeRequest([
        'message' => $articleText
    ]);

    $apiInstance->invokeFlowResponse($sessionId, $invokeRequest);

    echo "✓ Session invoked successfully!\n\n";

    // STEP 3: Poll for invocation response
    echo "Step 3: Polling for response...\n";
    echo "Waiting 5 seconds before first check...\n";
    sleep(5);

    $maxAttempts = 60;
    $attempt = 0;
    $fromTimestamp = 0;
    $aiMessages = [];
    $failed = false;
    $emptyAttempts = 0;
    $korekturaFileUrl = null;
    $korekturaFileName = null;

    while ($attempt < $maxAttempts && !$failed && !$korekturaFileUrl) {
        $attempt++;

        try {
            // Poll for response using timestamp
            $events = $apiInstance->pollFlowResponse($sessionId, $fromTimestamp);

            // Check if we have events
            if ($events && is_array($events) && count($events) > 0) {
                $emptyAttempts = 0;
                echo "Attempt $attempt/$maxAttempts - Got " . count($events) . " events\n";

                foreach ($events as $event) {
                    $actionType = (string) $event->getActionType();
                    $eventType = (string) $event->getEventType();
                    $timestamp = $event->getCreatedAtTimestamp();

                    // Update timestamp for next poll
                    if ($timestamp) {
                        $fromTimestamp = $timestamp;
                    }

                    // Collect AI messages
                    if ($eventType == 'ai' && $actionType == 'message') {
                        $metadata = $event->getMetadata();
                        if ($metadata && $metadata->getMessage()) {
                            $aiMessages[] = $metadata->getMessage();
                        }
                    }

                    // Check for artifacts with "korektura" in the name
                    if ($actionType === 'artefacts') {
                        // Access metadata and get artefacts
                        $metadata = $event->getMetadata();
                        if ($metadata && method_exists($metadata, 'getArtefacts')) {
                            $artefacts = $metadata->getArtefacts();

                            if ($artefacts && is_array($artefacts)) {
                                foreach ($artefacts as $artefact) {
                                    // Check if artifact is a FlowSessionArtefactInfo instance
                                    if ($artefact instanceof FlowSessionArtefactInfo) {
                                        $fileName = $artefact->getName();

                                        // Check if this is the korektura file
                                        if (stripos($fileName, 'korektura') !== false) {
                                            // Get download URL from SDK
                                            $downloadUrl = $artefact->getDownloadUrl();
                                            if ($downloadUrl) {
                                                $korekturaFileUrl = $downloadUrl;
                                                $korekturaFileName = $fileName;
                                            } else {
                                                echo "\n⚠ Warning: Korektura file found but download URL is empty\n";
                                            }

                                            echo "\n✓ Found korektura file: $fileName\n";
                                            break 2; // Break out of both foreach loops
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Check if flow failed
                    if ($actionType == 'failed') {
                        echo "\n✗ Flow failed!\n";
                        print_r($event);
                        $failed = true;
                        break;
                    }
                }
            } else {
                $emptyAttempts++;
                echo "Attempt $attempt/$maxAttempts - No new events (empty: $emptyAttempts)\n";

                // If we got AI messages and haven't seen events in 3 attempts, likely complete
                if (count($aiMessages) > 0 && $emptyAttempts >= 3) {
                    echo "\n✓ Flow completed successfully!\n\n";
                    break;
                }
            }

            // Wait before next attempt
            if ($attempt < $maxAttempts && !$failed && !$korekturaFileUrl) {
                sleep(5);
            }

        } catch (Exception $e) {
            echo "Attempt $attempt/$maxAttempts - Error: " . $e->getMessage() . "\n";

            if ($attempt < $maxAttempts && !$failed && !$korekturaFileUrl) {
                sleep(5);
            }
        }
    }

    // Download and display korektura file if found
    if ($korekturaFileUrl) {
        echo "\n=== DOWNLOADING KOREKTURA FILE ===\n";
        echo "File: $korekturaFileName\n";
        echo "URL: $korekturaFileUrl\n\n";

        try {
            $client = new Client();
            $response = $client->request('GET', $korekturaFileUrl);
            $fileContent = $response->getBody()->getContents();

            echo "=== KOREKTURA RESULTS ===\n\n";

            // Try to decode as JSON
            $jsonData = json_decode($fileContent, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
            } else {
                // If not JSON, display as plain text
                echo $fileContent . "\n";
            }

            echo "\n✓ Korektura file processed successfully!\n";

        } catch (Exception $e) {
            echo "✗ Error downloading korektura file: " . $e->getMessage() . "\n";
        }
    }

    // Display results
    if (!$failed && !$korekturaFileUrl) {
        echo "=== OUTPUT DATA ===\n";
        if (count($aiMessages) > 0) {
            foreach ($aiMessages as $idx => $message) {
                echo "\nMessage " . ($idx + 1) . ":\n";
                echo $message . "\n";
            }
        } else {
            echo "No AI messages collected.\n";
        }
    }

    if ($attempt >= $maxAttempts && !$korekturaFileUrl) {
        echo "\n⚠ Timeout: Flow did not complete within expected time.\n";
        echo "Session ID: $sessionId\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Exception class: " . get_class($e) . "\n";

    if (method_exists($e, 'getResponseBody')) {
        echo "Response body: " . $e->getResponseBody() . "\n";
    }

    exit(1);
}
