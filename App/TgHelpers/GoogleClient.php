<?php

namespace App\TgHelpers;

use DateTime;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

define('SITE_ROOT', $_SERVER['DOCUMENT_ROOT']);

require __DIR__ . '/../../vendor/autoload.php';

class GoogleClient {

    function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig('credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = 'token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = '4/wQHx5Nc19esvz4lDrbptaUcrGXtSXkgncTqsyUyknVlorp0lPrJgvDo';

                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }

    function delete($eventId)
    {
        if ($eventId) {
            $client = $this->getClient();
            $service = new Google_Service_Calendar($client);
            $service->events->delete('irynazaporozhets@gmail.com', $eventId);
        }
    }

    function create($title, $start, $end, $admin = false, $newUser = false)
    {
        if ($admin) {
            $colorId = 3;
        } elseif ($newUser) {
            $colorId = 4;
        } else {
            $colorId = 6;
        }

        $client = $this->getClient();
        $service = new Google_Service_Calendar($client);
        $event = new Google_Service_Calendar_Event([
            'summary' => $title,
            'start' => [
                'dateTime' => $start,
            ],
            'end' => [
                'dateTime' => $end,
            ],
            // 6 - orange - bot
            // 4 - new users
            // 3 - purple - admin
            'colorId' => $colorId
        ]);
        $calendarId = 'irynazaporozhets@gmail.com';
        $result = $service->events->insert($calendarId, $event);

        return $result->id;
    }

    function getRecords($start_search, $end_search, $data, $aid = '')
    {
        $client = $this->getClient();
        $service = new Google_Service_Calendar($client);

        $calendarId = 'irynazaporozhets@gmail.com';
        $optParams = [
            'maxResults' => 200,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => $start_search,
            'timeMax' => $end_search,
        ];
        $results = $service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();
        $event_list = [];
        if (empty($events)) {
            return false;
        } else {
            foreach ($events as $event) {
                $start = $event->start->dateTime;
                $end = $event->end->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                $event_list[] = [
                    'start' => $start,
                    'end' => $end
                ];
            }
        }

        return $this->getFree($start_search, $end_search, $event_list, $data, $aid);
    }

    private function getFree($start, $end, $events, $data, $aid = '')
    {
        $selected_day = $data[0]['selected_day'];
        $selected_time = $data[0]['selected_time'];
        $how_long = $data[0]['how_long'];

        $start = strtotime($start);

        $end = strtotime($end);
        $result = [];
        // Kick off first appt time at beginning of the day.
        $appt_start_time = $start;

        $i = 0;
        $max = 0;
// Loop through each appt slot in the search range.
        while ($appt_start_time < $end) {
            // Add 29:59 to the appt start time so we know where the appt will end.
            $appt_end_time = ($appt_start_time + 1799);
            // For each appt slot, loop through the current appts to see if it falls in a slot that is already taken.
            $slot_available = true;
            foreach ($events as $event) {
                $event_start = strtotime($event['start']);
                $event_end = strtotime($event['end']);

                // If the appt start time or appt end time falls on a current appt, slot is taken.
                if (($appt_start_time >= $event_start && $appt_start_time < $event_end) ||
                    ($appt_end_time >= $event_start && $appt_end_time < $event_end) &&
                    (strtotime('+' . $how_long . ' minutes', $appt_start_time) < $event_end)) {
                    $slot_available = false;
                    break; // No need to continue if it's taken.
                }
            }

            // If we made it through all appts and the slot is still available, it's an open slot.
            if ($slot_available) {
                $date = new DateTime();
                $date->setTimestamp($appt_start_time);
                if ($selected_day === '0') {
                    //будний
                    if ($date->format('l') == 'Tuesday' || $date->format('l') == 'Thursday') {
                        if ($how_long == 150 || $how_long == 180) {
                            if ($date->format('H') <= 17) {
                                if ($date->format('i') == '00' || $date->format('i') == '30') {
                                    $result[] = $date->format("Y-m-d H:i");
                                }
                            }
                        } elseif ($date->format('H') >= 9 && $date->format('H') <= 19) {
                            $result[] = $date->format("Y-m-d H:i");
                        }
                    }
                } elseif ($selected_day === '1') {
                    //суббота
                    if ($date->format('l') == 'Saturday') {
                        if ($how_long == 150 || $how_long == 180) {
                            if ($date->format('H') <= 17) {
                                if ($date->format('i') == '00' || $date->format('i') == '30') {
                                    $result[] = $date->format("Y-m-d H:i");
                                }
                            }
                        } elseif ($date->format('H') >= 9 && $date->format('H') <= 19) {
                            $result[] = $date->format("Y-m-d H:i");
                        }
                    }
                } elseif ($selected_day === '2') {
                    //неважно
                    if ($date->format('l') == 'Tuesday' || $date->format('l') == 'Thursday' || $date->format('l') == 'Saturday') {
                        if ($how_long == 150 || $how_long == 180) {
                            if ($date->format('H') <= 17) {
                                if ($date->format('i') == '00' || $date->format('i') == '30') {
                                    $result[] = $date->format("Y-m-d H:i");
                                }
                            }
                        } elseif ($date->format('H') >= 9 && $date->format('H') <= 19) {
                            $result[] = $date->format("Y-m-d H:i");
                        }
                    }
                }
            }

            $appt_start_time += (60 * ($how_long == 30 ? 30 : 60));
        }

        $time_arr = [
            '1' => [
                'start' => 9,
                'end' => 12
            ],
            '2' => [
                'start' => 12,
                'end' => 17
            ],
            '3' => [
                'start' => 17,
                'end' => 19
            ],
            '4' => [
                'start' => 9,
                'end' => 19
            ],
        ];
        $acc_result = [];
        foreach ($result as $time) {
            if (date('H', strtotime($time)) >= $time_arr[$selected_time]['start'] && date('H', strtotime($time)) < $time_arr[$selected_time]['end']) {
                $acc_result[] = [
                    'title' => $time,
                    'id' => $time,
                    'aid' => $aid,
                ];
            }
        }

        $next_result = [];
        if ($how_long >= 120) {
            foreach ($acc_result as $acc_item) {
                $acc_start = strtotime($acc_item['title']);
                $acc_end = strtotime($acc_item['title']) + (60 * $how_long);
                foreach ($events as $key => $event) {
                    if (date('Y', strtotime($events[$key + 1]['start'])) >= '2020') {
                        $next_event_start = strtotime($events[$key + 1]['start']);
                    } else {
                        //    error_log(json_encode($event['start']));
                        $next_event_start = strtotime($event['start']);
                    }
                    $event_end = strtotime($event['end']);
                    if ($acc_start >= $event_end && $acc_end <= $next_event_start) {
                        if (date('H', $acc_start) >= $time_arr[$selected_time]['start'] && date('H', $acc_start) <= $time_arr[$selected_time]['end']) {
                            $next_result[] = [
                                'title' => date('Y-m-d H:i', $acc_start),
                                'id' => date('Y-m-d H:i', $acc_start),
                                'aid' => $aid,
                            ];
                            break;
                        }
                    } elseif (date('m-d', $next_event_start) == '04-02') {
                        if (date('H', $acc_start) == '15' || date('H', $acc_start) == '16' || date('H', $acc_start) == '17') {
                            $next_result[] = [
                                'title' => date('Y-m-d H:i', $acc_start),
                                'id' => date('Y-m-d H:i', $acc_start),
                                'aid' => $aid,
                            ];
                            break;
                        }
                    }
                }
            }
            return $next_result;
        } else {
            return $acc_result;
        }
    }

}
