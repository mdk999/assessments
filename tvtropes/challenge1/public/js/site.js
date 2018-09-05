$(document).ready(function() {

    $('#srchterm').keypress(function(e) {
        var key = e.which;
        if (key == 13) // the enter key code
        {
            $('#srchbtn').click();
            return false;
        }
    });


    $('#srchbtn').click(function() {

        var term = $('#srchterm').val().replace(' ', '+');

        displayResults(term);

    });

    function displayResults(term) {

        $.getJSON('/challenge1/_tvdb.php?action=search&term=' + term, function(data) {

            var tmp = data;

            var res = $('#result-container');

            var theTemplateScript = $("#search-results").html();

            //Compile the template​
            var theTemplate = Handlebars.compile(theTemplateScript);

            $('.search-result').remove();

            res.append(theTemplate(tmp.data));

        });

    }


    $('#result-container').on('click', '.btn-show-episodes', function(evt) {

        const seriesId = $(this).data('episodes');

        const seriesContainer = $('div[data-episodes-id="' + seriesId + '"]');

        // clear other episodes list
        hideAllEpisodesExceptFor(seriesId);

        // Toggle episodes of current search results, if any.
        const episodesRendered = seriesContainer.find('.episode').length;


        if (episodesRendered > 0 && seriesContainer.hasClass('hidden')) {

            seriesContainer.removeClass('hidden');

        } else seriesContainer.addClass('hidden');

    });

    function hideAllEpisodesExceptFor(seriesId) {

        $('.search-result').each(function(idx, el) {

            if ($(el).data('series-id') !== seriesId) $(el).find('.episodes-container').addClass('hidden');

        });
    }

});