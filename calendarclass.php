<?php
/*Wren Miles for Regional Arts and Culture Council 2024*/
class Calendar {
    //create private variables to hold events and present date
    private $active_year, $active_month, $active_day;
    private $events = [];
    //constructor, if the date is empty then get the date, otherwise reformat to today
    public function __construct($date = null) {
        $this->active_year = $date != null ? date('Y', strtotime($date)) : date('Y');
        $this->active_month = $date != null ? date('m', strtotime($date)) : date('m');
        $this->active_day = $date != null ? date('d', strtotime($date)) : date('d');
    }
    //fill up the event variable attributes, this will be the main thing that needs to change 
    public function add_event($title, $txt, $date, $time, $icon, $location, $more, $days = 1, $color = '') {
        $color = $color ? ' ' . $color : $color;
        $this->events[] = [$title, $txt, $date, $time, $icon, $location, $more, $days, $color];
    }

    public function __toString() {
        //gets the number of days in the current and last month
        $num_days = date('t', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year));
        $num_days_last_month = date('j', strtotime('last day of previous month', strtotime($this->active_day . '-' . $this->active_month . '-' . $this->active_year)));
        $days = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        //finds what day of the week the month starts on 
        $first_day_of_week = array_search(date('D', strtotime($this->active_year . '-' . $this->active_month . '-1')), $days);
        $html = '<div class="calendar">';
        $html .= '<div class="header">';
        $html .= '<div class="month-year">';
        //gets month and year
        $html .= date('F Y', strtotime($this->active_year . '-' . $this->active_month . '-' . $this->active_day));
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="days">';
        //make a div for each day of the week 
        foreach ($days as $day) {
            $html .= '
                <div class="day_name">
                    ' . $day . '
                </div>
            ';
        }
        //fills in days of the week from the last month but they won't display any events
        for ($i = $first_day_of_week; $i > 0; $i--) {
            $html .= '
                <div class="day_num ignore">
                    ' . ($num_days_last_month-$i+1) . '
                </div>
            ';
        }
        //for each day in the month
        for ($i = 1; $i <= $num_days; $i++) {
            $selected = '';
            if ($i == $this->active_day) {
                $selected = ' selected';
            }
            $html .= '<div class="day_num' . $selected . '">';
            $html .= '<span>' . $i . '</span>';
            //for each event in the calendar
            foreach ($this->events as $event) {
                //for as many days as the current event goes
                for ($d = 0; $d <= ($event[7]-1); $d++) {
                    if (date('y-m-d', strtotime($this->active_year . '-' . $this->active_month . '-' . $i . ' -' . $d . ' day')) == date('y-m-d', strtotime($event[2]))) {
                        $html .= '<div class="event' . $event[8] . '">';
                        $html .= '<div class="image">';
                        $html .= $event[4];
                        $html .= '</div>';
                        $html .= $event[0];
                        $html .= '<div class="details">';
                        $html .= $event[3];
                        $html .= '</div>';
                        $html .= '<div class="location">';
                        $html .= $event[6];
                        $html .= '</div>';
                        $html .= '<div class="more">';
                        $html .= $event[5];
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                }
            }
            $html .= '</div>';
        }
        for ($i = 1; $i <= (35-$num_days-max($first_day_of_week, 0)); $i++) {
            $html .= '
                <div class="day_num ignore">
                    ' . $i . '
                </div>
            ';
        }
        $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

}
?>