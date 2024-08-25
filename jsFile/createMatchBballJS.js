$(document).ready(function () {
    let team1SelectedAthletes = []; // Array for TEAM-1
    let team2SelectedAthletes = []; // Array for TEAM-2
    let generalSelectedAthletes = []; // Array for all selected athletes
    let currentTeam = 'TEAM-1'; // Default team

    // Function to fetch and display athlete data
    function fetchAthletes(position) {
        $.ajax({
            url: '../onloadFunction/getAthletesCoach.php',
            type: 'GET',
            data: { position: position },
            dataType: 'json',
            success: function (data) {
                let cardContainer = $('#athleteCardContainer');
                cardContainer.empty(); // Clear existing cards
    
                let row = $('<div class="row"></div>'); // Create a row for the cards
    
                data.forEach(function (athlete) {
                    let isChecked = (currentTeam === 'TEAM-1' ? team1SelectedAthletes : team2SelectedAthletes).includes(athlete.ath_email) ? 'selected' : 'not-selected';
                    let isDisabled = generalSelectedAthletes.includes(athlete.ath_email) ? 'disabled' : '';
                    
                    // Assuming ath_img contains just the filename, prepend the path to the images directory
                    let imagePath = `../../images/profile/${athlete.ath_img}`;
                    
                    let card = `
                        <div class="col-sm-6 col-md-4 col-lg-2 mb-2 athlete-card ${isDisabled}" data-email="${athlete.ath_email}" data-name="${athlete.ath_first} ${athlete.ath_last}" data-image="${athlete.ath_img}" data-height="${athlete.ath_height}" data-weight=" ${athlete.ath_weight}" data-position=" ${athlete.ath_position}" data-sport="${athlete.ath_sport}">
                            <div class="card custom-card ${isChecked}">
                                <img src="${imagePath}" class="card-img-top" alt="${athlete.ath_first} ${athlete.ath_last}">
                                <div class="card-body p-2">
                                    <h6 class="card-title">${athlete.ath_first} ${athlete.ath_last}</h6>
                                    <p class="card-text">${athlete.ath_position}</p>
                                    <div class="athlete-info">
                                        <p class="card-text"><strong>Height:</strong> ${athlete.ath_height} CM</p>
                                        <p class="card-text"><strong>Weight:</strong> ${athlete.ath_weight} KG</p>
                                    </div>
                                </div>
                            </div>
                        </div>`;
    
                    row.append(card); // Append each card to the row
                });
    
                cardContainer.append(row); // Append the row to the container
    
                // Bind events after updating the cards
                $('.athlete-card').on('click', updateSelectedAthletes);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching athletes data: ", error);
            }
        });
    }

    function updateSelectedAthletes() {
        let card = $(this);
        let athleteEmail = card.data('email');
        let athleteName = card.data('name');
        let athleteSport = card.data('sport');

        // Determine the current selection array based on team
        let selectedAthletesArray = (currentTeam === 'TEAM-1') ? team1SelectedAthletes : team2SelectedAthletes;

        // Check if selection limit is reached
        if (selectedAthletesArray.length >= 8 && !selectedAthletesArray.includes(athleteEmail)) {
            alert('8 players can only be selected');
            return; // Exit function without making changes
        }

        // Toggle selection
        if (selectedAthletesArray.includes(athleteEmail)) {
            selectedAthletesArray = selectedAthletesArray.filter(email => email !== athleteEmail);
            card.removeClass('selected').addClass('not-selected');
            // Remove from general selected athletes
            generalSelectedAthletes = generalSelectedAthletes.filter(email => email !== athleteEmail);
            card.removeClass('disabled'); // Re-enable the athlete
        } else {
            selectedAthletesArray.push(athleteEmail);
            card.removeClass('not-selected').addClass('selected');
            // Add to general selected athletes
            if (!generalSelectedAthletes.includes(athleteEmail)) {
                generalSelectedAthletes.push(athleteEmail);
            }
            card.addClass('disabled'); // Disable the athlete
        }

        // Update the array based on the current team
        if (currentTeam === 'TEAM-1') {
            team1SelectedAthletes = selectedAthletesArray;
        } else {
            team2SelectedAthletes = selectedAthletesArray;
        }

        // Update selected athletes list
        updateSelectedAthletesList();
    }

    function updateSelectedAthletesList() {
        let selectedAthletes = [];
        let selectedAthletesArray = (currentTeam === 'TEAM-1') ? team1SelectedAthletes : team2SelectedAthletes;
        
        selectedAthletesArray.forEach(email => {
            let athlete = $('.athlete-card[data-email="' + email + '"]');

            // selectedAthletes.push(`<span class="selected-athlete-item" data-name="${athlete.data('name')}" data-sport="${athlete.data('sport')}">${athlete.data('name')} (${athlete.data('sport')})</span>`);

            selectedAthletes.push(`<div class="selected-athlete-item" data-name="${athlete.data('name')}" data-sport="${athlete.data('sport')}"">
                            <div class="card">
                                <img src="../../images/profile/${athlete.data('image')}" class="card-img-top" alt="${athlete.data('name')}">
                                <div class="card-body p-2">
                                    <h6 class="card-title">${athlete.data('name')}</h6>
                                    <p class="card-text">${athlete.data('position')}</p>
                                    <div class="athlete-info">
                                        <p class="card-text"><strong>Height:</strong> ${athlete.data('height')} CM</p>
                                        <p class="card-text"><strong>Weight:</strong> ${athlete.data('weight')} KG</p>
                                    </div>
                                </div>
                            </div>
                        </div>`);

        });

        $('#selectedAthletes').html(selectedAthletes.length > 0 ? selectedAthletes.join(' ') : '');
    }

    $(document).on('click', '.selected-athlete-item', function () {
        let athleteName = $(this).data('name');
        let athleteSport = $(this).data('sport');
        $(`.athlete-card[data-name="${athleteName}"][data-sport="${athleteSport}"]`).trigger('click');
    });

    $('#teamSelect').on('change', function () {
        currentTeam = $(this).val();
        fetchAthletes($('#positionSelect').val());
        updateSelectedAthletesList();
    });

    $('#positionSelect').on('change', function () {
        var selectedPosition = $(this).val();
        fetchAthletes(selectedPosition);
    });

    // Initial fetch for default view (show all athletes)
    fetchAthletes($('#positionSelect').val());
});
