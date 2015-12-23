<?php
namespace Src\Services;


use DateInterval;
use Google_Client;
use Google_Exception;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_ResourceId;
use Google_Service_YouTube_SearchResult;
use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoContentDetails;
use Google_Service_YouTube_VideoListResponse;

class YoutubeFetcher
{
    const NON_FETCHED_LOG = APP_PATH.'src/storage/logs/non-fetched.log';

    /**
     * Id youtube fetcher in debug mode
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Path where sound are saved to
     *
     * @var string
     */
    protected $path = APP_PATH.'src/storage/sounds/';

    /**
     * Should non fetched songs be logged
     *
     * @var bool
     */
    protected $logNonFetched = false;

    /**
     * @var Google_Service_YouTube
     */
    protected $ytService;

    function __construct()
    {
        $this->initYtService();
    }

    /**
     * Initialize youtube service, so we can search videos
     */
    protected function initYtService()
    {
        $client = new Google_Client();
        $client->setDeveloperKey(env('GOOGLE_API_KEY'));

        // Define an object that will be used to make all API requests.
        $this->ytService = new Google_Service_YouTube($client);
    }

    /**
     * Fetches mp3 by query in yt
     *
     * @param $query
     * @return bool
     */
    public function fetchByQuery($query)
    {
        $this->debug('Looking for: ' . $query);

        $videoId = $this->getYtVideoId($query);
        if ($videoId === false) {
            return false;
        }
        $this->debug('Found video id: ' . $videoId);

        $track = $this->downloadWithYtInMP3($videoId);

        // There is some error
        if ($track === false || strpos($track, 'html') !== false) {
            $this->logTrack($query);

            return false;
        }

        // Audio downloaded and saved
        if ($track === true) {
            return true;
        }

        // Save audio
        $path = realpath($this->path . '/' . str_replace('/', ' ', $query) . '.mp3');

        $this->debug('Saving to: ' . $path);
        file_put_contents($path, $track);

        return true;
    }

    /**
     * Gets an audio from youtube using youtubeinmp3.com
     *
     * @param string $videoId
     * @return bool|string
     */
    protected function downloadWithYtInMP3($videoId)
    {
        $link = 'http://www.youtubeinmp3.com/fetch/?format=JSON&video=http://www.youtube.com/watch?v=' . $videoId;

        $this->debug('Fetching from: ' . $link);
        $soundData = file_get_contents($link);
        $this->debug('Fetched: ' . $soundData);

        $soundData = json_decode($soundData, true);

        // If soundData is not array it means something went wrong
        if (!is_array($soundData)) {
            return false;
        }

        return file_get_contents($soundData['link']);
    }

    /**
     * Returns video url for a query
     *
     * @param string $query
     * @return string | bool
     */
    protected function getYtVideoId($query)
    {
        try {
            // Call the search.list method to retrieve results matching the specified
            // query term.
            $searchResponse = $this->ytService->search->listSearch('id,snippet', [
                'q'          => $query,
                'maxResults' => 1,
            ]);

            /** @var Google_Service_YouTube_SearchResult $item */
            $item = $searchResponse->getItems()[0];

            $itemId = $item->getId();

            /** @var Google_Service_YouTube_ResourceId $itemId */

            $videoId = $itemId->getVideoId();

            /** @var Google_Service_YouTube_VideoListResponse $videoListResponse */
            $videoListResponse = $this->ytService->videos->listVideos('contentDetails', [
                'id' => $videoId,
            ]);

            /** @var Google_Service_YouTube_Video $video */
            $video = $videoListResponse->getItems()[0];

            /** @var Google_Service_YouTube_VideoContentDetails $videoContentDetails */
            $videoContentDetails = $video->getContentDetails();

            $videoDuration = $videoContentDetails->getDuration();

            $interval = new DateInterval($videoDuration);
            $intervalSeconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
            if ($intervalSeconds > 60 * 20) {
                $this->debug('Fetched video is too long: ' . $videoDuration);

                return false;
            }

            return $videoId;
        } catch (Google_Service_Exception $e) {
            echo sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
        } catch (Google_Exception $e) {
            echo sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
        }

        return false;
    }


    /**
     * Sets a path, where sounds should be saved
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = rtrim((string)$path, '/');
    }

    /**
     * @param boolean $logNonFetched
     */
    public function setLogNonFetched($logNonFetched = true)
    {
        $this->logNonFetched = (bool)$logNonFetched;
    }

    /**
     * Log non fetched file
     *
     * @param $track
     * @return bool
     */
    protected function logTrack($track)
    {
        if (!$this->logNonFetched) {
            return false;
        }

        $content = '';
        if (file_exists(self::NON_FETCHED_LOG)) {
            $content = file_get_contents(self::NON_FETCHED_LOG);
            if (strlen($content)) {
                $content = rtrim($content, PHP_EOL);
                $content .= PHP_EOL;
            }
        }

        file_put_contents(self::NON_FETCHED_LOG, $content . $track . PHP_EOL);

        return true;
    }

    /**
     * Add debug info
     *
     * @param $message
     * @param string $status
     */
    protected function debug($message, $status = 'error')
    {
        if (!$this->debug) {
            return;
        }

        echo 'DEBUG: ' . $message . PHP_EOL;
    }

}
