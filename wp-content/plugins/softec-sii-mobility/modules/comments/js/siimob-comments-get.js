jQuery.noConflict()(function($) {
  "use strict";
  $(document).ready(function() {
    var postsOffset = 20;
    var isProcessing = false;

    /*
     * Prende gli ultimi 20 commenti inseriti con una chiamata AJAX a Wordpress
     * Reimposta postsOffset e isProcessing nel caso l'utente avesse effttuato uno scroll
     * sulla pagina ed avesse visto più di 20 commenti
     */
    siimobComments.getLastComments = function() {
      var commentResultRow = "";

      postsOffset = 20;
      isProcessing = false;

      $.ajax({
        type: "GET",
        url: siimobComments.ajaxUrl,
        data: {
          action: "siimob_comments_get"
        },
        dataType: "json",
        beforeSend: function() {
          // Mostra il box degli ultimi commenti con uno spinner ad indicare il caricamento
          $("#last-comments").empty().addClass("spinner");
        },
        success: function(result) {
          if(result.length > 0) {
            $.each(result, function(i) {
              var commentRowServiceName = result[i].service_name;
              var commentRowServiceUri  = result[i].service_uri;
              var commentRowPhotoThumb  = result[i].photo_thumb;
              var commentRowCivic       = result[i].civic;
              var commentRowTypeLabel   = result[i].type_label;
              var commentRowStars       = result[i].stars;
              var commentRowComment     = result[i].comment;
              var commentRowTimestamp   = result[i].timestamp;

              var imgSrc = "";
              var imgStars = "";

              if (commentRowPhotoThumb != "") {
                imgSrc = commentRowPhotoThumb;
              } else {
                imgSrc = siimobComments.pluginUrl + "/img/no-thumb.png";
              }

              for (var i = 1; i <= 5; i++) {
                var imgClass = (i <= commentRowStars) ? "gold-star" : "grey-star";
                imgStars += "<div class='" + imgClass + "' />";
              }

              commentResultRow += "<div class='comment-row'>" +
                                    "<h3>" + commentRowServiceName + " - " + commentRowCivic + " - " + commentRowTypeLabel + "</h3>" +
                                    /*"<img src='" + imgSrc + "' class='photo-thumb' alt='" + commentRowServiceName + "' />" +*/
                                    "<div class='comment-header'>" +
                                      "<div class='date-time-field'>" + commentRowTimestamp + "</div>" +
                                      "<div class='stars-field'>" + imgStars + "</div>" +
                                      "<div class='clear' />" +
                                    "</div>" +
                                    commentRowComment +
                                  "</div>";
            });
          } else {
            commentResultRow = "<div class='no-results'>Nessun commento inserito</div>";
          }
        },
        error: function() {
          commentResultRow = "<div class='error-result'>Errore nel caricamento dei commenti</div>";
        },
        complete: function() {
          // Rimuove lo spinner e inserisce i risultati nel box degli ultimi commenti
          $("#last-comments").removeClass("spinner").append(commentResultRow);
        }
      });
    }

    /*
     * Calcola l'altezza della pagina in cui si trova l'utente
     * Se l'utente si trova all'80% dello scroll della pagina, effettua una chiamata AJAX a Wordpress
     * per visualizzare altri 20 commenti e li inserisce successivamente a quelli già visualizzati
     */
    $(document).scroll(function() {
      var scrollAmount = $(window).scrollTop();
      var documentHeight = $(document).height();
      var scrollPercent = (scrollAmount / documentHeight) * 100;

      if(scrollPercent > 80 && !isProcessing) {
        /*
         * isProcessing è la variabile che indica che è in corso la chiamata AJAX per richiamare altri 20 commenti
         * da Wordpress e viene reimpostata a false quando la chiamata va buon fine ed esistono altri commenti da
         * visualizzare. La variabile rimane impostata a true se non esistono altri commenti da visualizzare e viene
         * reinizializzata a false quando viene chiamata la funzione getLastComments()
         */
        isProcessing = true;
        var scrolledCommentRow = "";

        $.ajax({
          type: "GET",
          url: siimobComments.ajaxUrl,
          data: {
            action: "siimob_comments_get",
            offset: postsOffset
          },
          dataType: "json",
          beforeSend: function() {
            // Aggiunge un div con spinner in fondo alla lista commenti
            $("#last-comments").append("<div class='scroll-load spinner' />");
          },
          success: function(result) {
            if(result.length > 0) {
              $.each(result, function(i) {
                var commentRowServiceName = result[i].service_name;
                var commentRowServiceUri  = result[i].service_uri;
                var commentRowPhotoThumb  = result[i].photo_thumb;
                var commentRowCivic       = result[i].civic;
                var commentRowTypeLabel   = result[i].type_label;
                var commentRowStars       = result[i].stars;
                var commentRowComment     = result[i].comment;
                var commentRowTimestamp   = result[i].timestamp;

                var imgSrc = "";
                var imgStars = "";

                if (commentRowPhotoThumb != "") {
                  imgSrc = commentRowPhotoThumb;
                } else {
                  imgSrc = siimobComments.pluginUrl + "/img/no-thumb.png";
                }

                for (var i = 1; i <= 5; i++) {
                  var imgClass = (i <= commentRowStars) ? "gold-star" : "grey-star";
                  imgStars += "<div class='" + imgClass + "' />";
                }

                scrolledCommentRow += "<div class='comment-row'>" +
                                          "<h3>" + commentRowServiceName + " - " + commentRowCivic + " - " + commentRowTypeLabel + "</h3>" +
                                          "<img src='" + imgSrc + "' class='photo-thumb' alt='" + commentRowServiceName + "' />" +
                                          "<div class='comment-header'>" +
                                            "<div class='date-time-field'>" + commentRowTimestamp + "</div>" +
                                            "<div class='stars-field'>" + imgStars + "</div>" +
                                            "<div class='clear' />" +
                                          "</div>" +
                                          commentRowComment +
                                        "</div>";
              });

              // Incrementa l'offset per la prossima chiamata AJAX e reimposta isProcessing a false
              postsOffset += 20;
              isProcessing = false;
            }
          },
          error: function() {
            scrolledCommentRow = "<div class='error-result'>Errore nel caricamento dei commenti</div>";
          },
          complete: function() {
            // Rimouve il div con spinner e accoda i risultati nel box degli ultimi commenti
            $("div").remove(".scroll-load.spinner");
            $("#last-comments").append(scrolledCommentRow);
          }
        });
      }
    });

    // Richiama gli ultimi 20 commenti inseriti una volta che la pagina è stata caricata
    siimobComments.getLastComments();
  });
});