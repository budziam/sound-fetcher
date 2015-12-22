<?php

use Src\Fetchers\IStreamFetcher;

/*
 * Downloading many sounds requires much time
 */
set_time_limit(0);

/*
 * Start fetching songs
 */
(new IStreamFetcher())->run();