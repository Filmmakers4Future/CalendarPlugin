<?php

  /**
    This is the Calendar plugin.

    @package Filmmakers4Future/CalendarPlugin
    @version 0.2
    @author  Paul-Vincent Roll <paul-vincent@filmmakersforfuture.org>
    @since   0.2
  */

  // ===== DO NOT EDIT HERE =====

  // prevent script from getting called directly
  if (!defined("URLAUBE")) { die(""); }

  class CalendarPlugin extends BaseSingleton implements Plugin {

    // CONSTANTS
    // Content 
    
    const CALENDAR_PLACEHOLDER = "[calendar]";
    
    // CONSTANTS
    // config 
    const CALENDAR_DATA = "CALENDAR_DATA";
    const CALENDAR_NO_ENTRIES_TEXT = "CALENDAR_NO_ENTRIES_TEXT";
    const CALENDAR_BUTTON_SHOW_PAST_TEXT = "CALENDAR_BUTTON_SHOW_PAST_TEXT";
    const CALENDAR_BUTTON_HIDE_PAST_TEXT = "CALENDAR_BUTTON_HIDE_PAST_TEXT";
    const CALENDAR_TIMEZONE = "CALENDAR_TIMEZONE";
  
    protected static function configure() {
       Plugins::preset(static::CALENDAR_DATA, null);
       Plugins::preset(static::CALENDAR_NO_ENTRIES_TEXT, "No entries.");
       Plugins::preset(static::CALENDAR_BUTTON_SHOW_PAST_TEXT, "Show past");
       Plugins::preset(static::CALENDAR_BUTTON_HIDE_PAST_TEXT, "Hide past");
       Plugins::preset(static::CALENDAR_TIMEZONE, new DateTimeZone('Europe/Berlin'));
    }
    
    public static function addTimezoneConversion() {
      print(fhtml("<!-- Timezone conversion -->").NL);
      print(fhtml("<script>").NL);
      print(fhtml("var dates = document.getElementsByClassName(\"date\");").NL);
      print(fhtml("for (date of dates) {
                     time = new Date(date.dataset.time);
                    date.innerHTML = time.toLocaleString(navigator.userLanguage || navigator.language, {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', weekday: 'long'});;
                 }".NL));
      print(fhtml("</script>").NL);
      
    }
    
    protected static function getCalendar($item) {
      // preset plugin configuration
      static::configure();
      
      $result = value($item, CONTENT);
      $eventsDisplayed = 0;
      
      if (is_string($result)) {
        if (Plugins::get(static::CALENDAR_DATA)) {
          $entries = Plugins::get(static::CALENDAR_DATA);
          
          $calendarHTML = fhtml("<div class=\"row justify-content-center %s\">".NL,
            "text-left");
          foreach ($entries as $entry) {
            
            $timezone = new DateTimeZone($entry["timezone"]);
            $date = DateTime::createFromFormat('d/m/Y H:i:s', $entry["date"], $timezone);
            $date->setTimezone(Plugins::get(static::CALENDAR_TIMEZONE));
            
            if (new DateTime() < $date or isset($_GET['showPast']) && (bool)$_GET['showPast'] == true) {
              $eventsDisplayed +=1; 
              
              $tags = "";
              
              foreach ($entry["tags"] as $tag) {
                $tags = $tags.fhtml("<span class=\"calendarTag\">%s</span>".NL, $tag);
              }
              
              $calendarHTML .= fhtml("<div class=\"col-xxxl-3 col-xxl-4 col-xl-5 col-lg-9 col-md-10 col-sm-10 col-10 mx-5 mb-3 calendarItem\">".NL);
              $calendarHTML .= fhtml("<div class=\"row justify-content-center align-items-center\">".NL);
              
              $calendarHTML .= fhtml("
                <div class=\"col-xl-3 col-lg-3 col-md-3 col-sm-3 col-6 mb-3 image\">
                  <img src=\"%s\" class=\"img-fluid\">
                </div>".NL, $entry["image"]);
              
              $calendarHTML .= fhtml("
                <div class=\"col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12 summary\">
                  <a href=\"%s\"><h4 class=\"text-white header\">%s</h4></a>
                  <p class=\"text-white date\" data-time=\"%s\">%s <small>UTC +1</small></p>
                  <p class=\"text-white text\">%s</p>".NL, $entry["link"], $entry["title"], $date->format('c'), date_format($date,"jS F Y H:i"), $entry["summary"]);
              $calendarHTML .= $tags;
              $calendarHTML .= fhtml("</div>".NL);
              
              $calendarHTML .= fhtml("</div>".NL);
              $calendarHTML .= fhtml("</div>".NL);
            }
          }
          $calendarHTML .= fhtml("</div>".NL);
        } 
        
        if ($eventsDisplayed == 0) {
          $calendarHTML = fhtml("<p>".Plugins::get(static::CALENDAR_NO_ENTRIES_TEXT)."</p>".NL);
        }
        
        if (count(Plugins::get(static::CALENDAR_DATA)) != $eventsDisplayed) {
          $calendarHTML .= fhtml("<a class=\"btn btn-primary btn-l\" href=\"?showPast=1\">%s</a>".NL, Plugins::get(static::CALENDAR_BUTTON_SHOW_PAST_TEXT));
        } elseif (isset($_GET['showPast']) && (bool)$_GET['showPast']) {
          $calendarHTML .= fhtml("<a class=\"btn btn-primary btn-l\" href=\"%s\">%s</a>".NL, rtrim(explode("?", $_SERVER['REQUEST_URI'])[0],"/"), Plugins::get(static::CALENDAR_BUTTON_HIDE_PAST_TEXT));
        }
        
    
        
        $result = str_ireplace(static::CALENDAR_PLACEHOLDER, $calendarHTML, $result);
        
        }

      return $result;
    }

    // RUNTIME FUNCTIONS

    public static function plugin($content) {
      $result = $content;

      if ($result instanceof Content) {
        if ($result->isset(CONTENT)) {
          $result->set(CONTENT, static::getCalendar($result));
        }
      } else {
        if (is_array($result)) {
          // iterate through all content items
          foreach ($result as $result_item) {
            if ($result_item instanceof Content) {
              if ($result_item->isset(CONTENT)) {
                $result_item->set(CONTENT, static::getCalendar($result_item));
              }
            }
          }
        }
      }

      return $result;
    }

  }

  // register plugin
  Plugins::register(CalendarPlugin::class, "plugin", FILTER_CONTENT);
  Plugins::register(CalendarPlugin::class, "addTimezoneConversion", AFTER_BODY);