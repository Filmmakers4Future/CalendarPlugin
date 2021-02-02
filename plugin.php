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

    const CALENDAR_DATES = "CalendarDates";
    
    const CALENDAR_DATA = [
      ["title" => "Awesome Meeting",
       "date" => "01/01/2021 15:16:17",
       "summary" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas congue condimentum dui, ut rhoncus erat posuere non. Ut dignissim in sem eget suscipit.",
       "image" => "https://picsum.photos/150",
       "tags" => [
          "German",
          "Meeting",
          "BBB"
        ]],
      ["title" => "Awesome Meeting",
        "date" => "02/02/2021 15:16:17",
        "summary" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas congue condimentum dui, ut rhoncus erat posuere non. Ut dignissim in sem eget suscipit.",
        "image" => "https://picsum.photos/150",
        "tags" => [
          "German",
          "Meeting",
          "BBB"
        ]],
      ["title" => "Awesome Meeting",
        "date" => "05/02/2021 15:16:17",
        "summary" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas congue condimentum dui, ut rhoncus erat posuere non. Ut dignissim in sem eget suscipit.",
        "image" => "https://picsum.photos/150",
        "tags" => [
          "German",
          "Meeting",
          "BBB"
        ]],
      ["title" => "Awesome Meeting",
        "date" => "20/02/2021 15:16:17",
        "summary" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas congue condimentum dui, ut rhoncus erat posuere non. Ut dignissim in sem eget suscipit.",
        "image" => "https://picsum.photos/150",
        "tags" => [
          "German",
          "Meeting",
          "BBB"
        ]]
    ];
  
    
    protected static function getCalendar($item) {
      $result = value($item, CONTENT);
      $calendarHTML = String;
      $eventsDisplayed = 0;
      
      if (is_string($result)) {
        $calendarData = json_decode(value($item, self::CALENDAR_DATES), TRUE);
        
        if ($calendarData) {
          $calendarHTML = fhtml("<div class=\"row justify-content-center %s\">".NL,
            "text-left");
          foreach ($calendarData as $ENTRY) {
            $date = DateTime::createFromFormat('d/m/Y H:i:s', $ENTRY["date"]);
            if (new DateTime() < $date or (bool)$_GET['showOld'] == true) {
              $eventsDisplayed +=1; 
              
              $tags = "";
              
              foreach ($ENTRY["tags"] as $tag) {
                $tags = $tags.fhtml("<span class=\"calendarTag\">%s</span>".NL, $tag);
              }
              
              $calendarHTML = $calendarHTML.fhtml("<div class=\"col-xxxl-3 col-xxl-4 col-xl-5 col-lg-9 col-md-10 col-sm-10 col-10 mx-5 mb-3 calendarItem\">".NL);
              
              $calendarHTML = $calendarHTML.fhtml("
                <div class=\"image\">
                  <img src=\"%s\">
                </div>".NL, $ENTRY["image"]);
              
              $calendarHTML = $calendarHTML.fhtml("
                <div class=\"summary\">
                  <a href=\"%s\"><h4 class=\"text-white header\">%s</h4></a>
                  <p class=\"text-white date\">%s <small>UTC +1</small></p>
                  <p class=\"text-white text\">%s</p>".NL, $ENTRY["link"], $ENTRY["title"], date_format($date,"jS F Y H:i"), $ENTRY["summary"]);
              
              $calendarHTML = $calendarHTML.$tags;
              
              $calendarHTML = $calendarHTML.fhtml("</div>".NL);
              
              $calendarHTML = $calendarHTML.fhtml("</div>".NL);
            }
          }
          
          if ($eventsDisplayed == 0) {
            $calendarHTML = fhtml("At the moment, there do not seem to be any events.");
          }
          
          $calendarHTML = $calendarHTML.fhtml("</div>");
        } else {
          $calendarHTML = fhtml("At the moment, there do not seem to be any events.");
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