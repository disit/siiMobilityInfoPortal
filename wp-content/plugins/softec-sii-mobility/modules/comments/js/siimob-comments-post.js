jQuery.noConflict()(function($) {
  "use strict";
  $(document).ready(function() {
    // Inizializza i box dei risultati e dei messaggi relativi al form al caricamento della pagina
    $("#result-box").hide().empty();
    $("#message-box").hide().empty();

    var keyUpTimeout = null;
    var messageTimeout = null;

    /*
     * Quando l'utente digita sul box di ricerca del servizio, una chiamata AJAX a Service Map mostrerà gli
     * eventuali risultati e l'utente potrà selezionare il servizio dalla lista mostrata
     */
    $("#service-name").keyup(function(e) {
      var isWordCharacter = e.key.length === 1;
      var isBackspaceOrSpace = (e.keyCode == 8 || e.keyCode == 32);

      // Continua nell'esecuzione solo se l'utente digita un carattere, spazio o backspace
      if (!isWordCharacter && !isBackspaceOrSpace) {
          return;
      }

      if (keyUpTimeout != null) {
        clearTimeout(keyUpTimeout);
      }

      /*
       * Esegue la chiamata a Service Map con un ritardo di 200 millisecondi in modo che la ricerca
       * non venga effettuara appena l'utente inizia a digitare nel campo di ricerca
       */
      keyUpTimeout = setTimeout(function() {
        keyUpTimeout = null;

        var searchInput = $("#service-name").val().trim();

        $("#result-box").hide().empty();

        // Se l'utente ha inserito almeno 3 caratteri, può partire la chiamata AJAX a Service Map
        if (searchInput.length > 2) {
          // Funzione di escape dei caratteri speciali delle regular expressions
          function escapeRegExp(str) {
            return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
          }

          var serviceResults = {};
          var resultRow = "";

          $.ajax({
            type: "GET",
            url: "http://servicemap.disit.org/WebAppGrafo/api/v1/?search=" + searchInput.replace(/ /g, "%20") + "&lang=it&format=json",
            beforeSend: function() {
              // Mostra il box dei risultati con uno spinner ad indicare il caricamento
              $("#result-box").show().addClass("spinner");
            },
            success: function(result) {
              serviceResults = result;

              if (serviceResults == null) {
                serviceResults = {
                  "fullCount": 0
                };
              }

              /*
               * Se sono presenti risultati da Service Map, li deserializzo e li visualizzo
               * nel box sottostante il campo di ricerca
               */
              if (serviceResults.fullCount > 0) {
                $.each(serviceResults.features, function(i) {
                  var serviceName = serviceResults.features[i].properties.name.toUpperCase();
                  var serviceUri  = serviceResults.features[i].properties.serviceUri;
                  var servicePhotoThumb   = serviceResults.features[i].properties.photoThumbs[0];
                  var serviceCivic        = serviceResults.features[i].properties.civic;
                  var serviceTypeLabel    = serviceResults.features[i].properties.typeLabel;

                  if (servicePhotoThumb == null) {
                    servicePhotoThumb = "";
                  }

                  // Escape quote singole e doppie dal nome del servizio
                  serviceName = serviceName.replace(/'/g, "&#39;");
                  serviceName = serviceName.replace(/"/g, "&#34;");

                  var searchInputSplitted = searchInput.toUpperCase().split(" ");
                  var serviceNameBolded   = serviceName;

                  // Mette in grassetto il testo dei risultati che corrispondono al testo ricercato dall'utente
                  $.each(searchInputSplitted, function(o) {
                    serviceNameBolded = serviceNameBolded.replace(
                      new RegExp(escapeRegExp(searchInputSplitted[o]), "g"),
                      "<strong>" + searchInputSplitted[o] + "</strong>"
                    );
                  });

                  resultRow += "<div class='result-row'>" +
                  "<a href='#' data-service-uri='" + serviceUri + "' data-service-name='" + serviceName + "' data-service-photo-thumb='" + servicePhotoThumb + "' data-service-civic='" + serviceCivic + "' data-service-type-label='" + serviceTypeLabel + "' title='" + serviceName + "' class='service-result'>" + serviceNameBolded + " - " + serviceCivic + " - " + serviceTypeLabel + "</a>" +
                  "</div>";
                });
              } else {
                resultRow = "<div class='no-results'>Nessun risultato disponibile</div>";
              }
            },
            error: function() {
              resultRow = "<div class='error-result'>Errore nel caricamento dei risultati</div>";
            },
            complete: function() {
              // Rimouve lo spinner e inserisce i risultati nel box dei risultati
              $("#result-box").removeClass("spinner").append(resultRow);
            }
          });
        }
      }, 300);
    });

    /*
     * Assegna ad un campo input hidden i dati del servizio selezionato come attributi per il successivo
     * invio tramite POST a Wordpress. Imposta il campo input text del servizio come readonly e inserisce
     * sulla destra di esso un'icona di cancellazione per rimuovere il servizio inserito nel campo input
     * text e i relativi dati dal campo input hidden
     */
    $("#result-box").on("click", "a.service-result", function(e) {
      e.preventDefault();
      var serviceName = $(this).attr("data-service-name");
      var serviceUri  = $(this).attr("data-service-uri");
      var servicePhotoThumb = $(this).attr("data-service-photo-thumb");
      var serviceCivic      = $(this).attr("data-service-civic");
      var serviceTypeLabel  = $(this).attr("data-service-type-label");

      $("#service-name").attr("readonly", "readonly").val(serviceName + " - " + serviceCivic + " - " + serviceTypeLabel);

      $("#service-data").attr({
        "data-service-name": serviceName,
        "data-service-uri": serviceUri,
        "data-service-photo-thumb": servicePhotoThumb,
        "data-service-civic": serviceCivic,
        "data-service-type-label": serviceTypeLabel
      });

      // Inserisce l'icona per la cancellazione del contenuto dell'input text con un onclick listener
      $("#service-name").wrap("<span class='delete-icon' />").after($("<span />").click(function() {
        $(this).prev("input").val("").trigger("change").focus();

        // Riuovo icona di cancellazione e i dati del servizio dall'input hidden
        $("span.delete-icon span").remove();
        $("#service-name").unwrap("<span class='delete-icon' />").removeAttr("readonly").val("");
        $("#service-data").removeAttr("data-service-name data-service-uri data-service-photo-thumb");
        $("#service-data").removeAttr("data-service-civic data-service-type-label");
      }));

      // Nasconde il box dei risultati
      $("#result-box").hide().empty();
    });

    /*
     * Cambia la for della label ogni volta che viene selezionato un
     * input radio differente per la valutazione del servizio
     */
    $("input[type='radio'][name='star']").on("click", function() {
      var starId = $(this).attr("id");
      $("label[for^='star-']:not([class^='star-'])").removeAttr("for").attr("for", starId);
    });

    // Reimposta i campi che il button type reset non reimposta di default
    $("#ratings-form-reset").on("click", function() {
      $(this).blur();

      if ($("#service-name").val() != "") {
        $("#result-box").hide().empty();
      }

      if ($("#service-name").attr("readonly") === "readonly") {
        $("span.delete-icon span").remove();
        $("#service-name").unwrap("<span class='delete-icon' />").removeAttr("readonly");
      }

      $("#service-data").removeAttr("data-service-name data-service-uri data-service-photo-thumb");
      $("#service-data").removeAttr("data-service-civic data-service-type-label");

      // Resetta il modulo di reCAPTCHA
      grecaptcha.reset();

      if ($("#message-box").is(":visible")) {
        $("#message-box").slideUp("fast", function() {
          $(this).empty();
        });
      }
    });

    /*
     * Effettua la validazione dei campi prima di inviare il form. Nel caso di campi non corretti o 
     * di errori lato server, visualizza un messaggio di errore tra la textarea dei commenti ed i
     * pulsanti di invio e reset
     */
    $("#ratings-form").submit(function(e) {
      e.preventDefault();

      $("#ratings-form-submit").blur();

      if ($("#message-box").is(":visible")) {
        $("#message-box").slideUp("fast");
      }

      $("#message-box").empty();

      var messageRow = "";
      var errorCounter = [];

      // Prende i valori inseriti nel form
      var serviceNameValue  = $("#service-data").attr("data-service-name");
      var serviceUriValue   = $("#service-data").attr("data-service-uri");
      var servicePhotoThumbValue  = $("#service-data").attr("data-service-photo-thumb");
      var serviceCivicValue       = $("#service-data").attr("data-service-civic");
      var serviceTypeLabelValue   = $("#service-data").attr("data-service-type-label");
      var starValue     = $("input:radio[name='star']:checked").val();
      var commentValue  = $("#comment").val();

      if (messageTimeout != null) {
        clearTimeout(messageTimeout);
      }

      // Controlla la validità dei valori inseriti nel form, nel caso di valori non validi inserisce l'errore in un array
      if (serviceNameValue === "" || serviceUriValue === "" || serviceNameValue == null || serviceUriValue == null || servicePhotoThumbValue == null || serviceCivicValue == null || serviceTypeLabelValue == null) {
        errorCounter.push("Inserisci un punto di interesse");
      }

      if (starValue == null) {
        errorCounter.push("Inserisci una valutazione");
      }

      if (commentValue.length < 3) {
        errorCounter.push("Inserisci un commento");
      }

      if (!(grecaptcha && grecaptcha.getResponse().length > 0)) {
        errorCounter.push("Spunta la casella <strong>Non sono un robot</strong>");
      }

      // Se l'array è vuoto allora non c'è nessun errore e può inviare i dati
      if (errorCounter.length === 0) {
        $.ajax({
          type: "POST",
          url: siimobComments.ajaxUrl,
          data: {
            action: "siimob_comment_post",
            service_name: serviceNameValue,
            service_uri: serviceUriValue,
            photo_thumb: servicePhotoThumbValue,
            civic: serviceCivicValue,
            type_label: serviceTypeLabelValue,
            stars: starValue,
            comment: commentValue,
            captcha: grecaptcha.getResponse()
          },
          dataType: "json",
          beforeSend: function() {
            // Mostra il box dei messaggi del form con uno spinner ad indicare il caricamento
            $("#message-box").addClass("spinner").slideDown("fast");
          },
          success: function(data) {
            if (data.saved) {
              // Il server invia una risposta corretta al salvataggio del commento
              $(".delete-icon span").remove();
              $("#service-name").unwrap("<span class='delete-icon' />").removeAttr("readonly").val("");
              $("input:radio[name='star']:checked").removeAttr("checked");
              $("#comment").val("");

              $("#service-data").removeAttr("data-service-name data-service-uri data-service-photo-thumb");
              $("#service-data").removeAttr("data-service-civic data-service-type-label");

              messageRow = "<div class='message-success'>Comento inviato con successo!</strong>";

              // Imposta un timeout che nasconde il messaggio di invio del commento dopo 5 secondi
              messageTimeout = setTimeout(function() {
                messageTimeout = null;
                $("#message-box").slideUp("slow");
              }, 5000);

              // Richiama gli ultimi commenti inseriti, tra cui appunto quello appena inviato
              siimobComments.getLastComments();
            } else {
              // Il server invia una risposta errata al salvataggio del commento
              messageRow = "<div class='message-error'>Errore nel salvataggio del commento</div>";
            }
          },
          error: function() {
            messageRow = "<div class='message-error'>Errore nell'invio del commento</div>";
          },
          complete: function() {
            // Rimouve lo spinner e inserisce i risultati nel box dei messaggi del form
            $("#message-box").removeClass("spinner").append(messageRow);

            // Resetta il modulo di reCAPTCHA
            grecaptcha.reset();
          }
        });
      } else {
        // Non invia nessun dato al server e mostra un errore con i campi non validi
        var errorRow = "<ul>";

        $.each(errorCounter, function(err) {
          errorRow += "<li>" + errorCounter[err] + "</li>";
        });

        errorRow += "</ul>";
        $("#message-box").append(errorRow).slideDown("fast");
      }
    });
  });
});
