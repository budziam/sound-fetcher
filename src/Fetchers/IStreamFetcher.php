<?php
namespace Src\Fetchers;
use Src\Services\YoutubeFetcher;


class IStreamFetcher
{

    /**
     * @var YoutubeFetcher
     */
    protected $ytFetcher;

    function __construct()
    {
        $this->ytFetcher = new YoutubeFetcher();
    }

    public function run()
    {
        while ($track = trim(fgets(STDIN))) {
            if ($this->ytFetcher->fetchByQuery($track)) {
                echo 'Track: ' . $track . ' was successfully downloaded' . PHP_EOL;
            } else {
                echo 'Something went wrong during downloading track: ' . $track . PHP_EOL;
            }
        }
    }

}