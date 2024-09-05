<!-- Modale per aggiungere una nuova manutenzione -->
<div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">Aggiungi Manutenzione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addServiceStatus"></div>
                <form id="addServiceForm" enctype="multipart/form-data">
                    <input type="hidden" name="vehicle_id" id="addServiceVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="addServiceDescription">Descrizione</label>
                        <input type="text" name="description" id="addServiceDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="addServiceAmount">Costo</label>
                        <input type="number" name="amount" id="addServiceAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="addServiceBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addServiceBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
						<label for="addServiceKilometers">Kilometri registrati</label>
						<input type="number" name="registered_kilometers" id="addServiceKilometers" class="form-control" required>
					</div>
					<div class="form-group mt-3 d-flex align-items-center">
						<label for="addServiceAttachment" class="btn btn-secondary" style="white-space: nowrap;">Scegli File</label>
						<input type="file" name="attachment" id="addServiceAttachment" class="form-control-file" style="display: none;">
						<input type="text" id="fileNameDisplay" class="form-control ml-2" placeholder="Nessun file selezionato" readonly>
					</div>
                    <div id="partsSection">
                        <h6>Parti utilizzate</h6>
                        <div id="addPartsContainer">
                            <!-- New parts will be dynamically inserted here -->
                        </div>
                        <button type="button" id="addNewPartBtn" class="btn btn-secondary btn-block">Aggiungi voce</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set the vehicle_id in the hidden field when the modal is shown
        $('#addServiceModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var vehicleId = button.data('vehicle-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#addServiceVehicleId').val(vehicleId);
        });

        // Add a new part row when "Aggiungi voce" is clicked
		$('#addNewPartBtn').click(function() {
			var partRow = `
				<div class="form-row align-items-end mb-2">
					<div class="col">
						<input type="text" name="part_name[]" class="form-control" placeholder="Nome prodotto" required>
					</div>
					<div class="col">
						<input type="text" name="part_number[]" class="form-control" placeholder="Codice prodotto" required>
					</div>
					<div class="col-auto">
						<button type="button" class="btn btn-danger removePartBtn">&times;</button>
					</div>
				</div>`;
			$('#addPartsContainer').append(partRow);
		});

        // Remove a part row when the remove button is clicked
        $(document).on('click', '.removePartBtn', function() {
            $(this).closest('.form-row').remove();
        });

        // Maximum file size in bytes (5MB = 5 * 1024 * 1024)
        var maxFileSize = 5 * 1024 * 1024;

        $('#addServiceForm').submit(function(e) {
            e.preventDefault();

            // Check file size before submitting
            var fileInput = $('#addServiceAttachment')[0];
            if (fileInput.files.length > 0) {
                var fileSize = fileInput.files[0].size;
                if (fileSize > maxFileSize) {
                    $('#addServiceStatus').html('<div class="alert alert-danger">Il file è troppo grande. La dimensione massima è 5MB.</div>');
                    return;  // Stop form submission
                }
            }

            // Basic client-side validation
            let isValid = true;
            $('#addServiceForm input[required]').each(function() {
                if ($(this).val() === '') {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                $('#addServiceStatus').html('<div class="alert alert-danger">Per favore compila tutti i campi obbligatori.</div>');
                return;
            }

            $('button[type="submit"]').prop('disabled', true);

            var formData = new FormData($('#addServiceForm')[0]);

            $.ajax({
                url: 'addService.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#addServiceStatus').html('<div class="alert alert-success">Manutenzione aggiunta con successo!</div>');

                        // If parts were added, submit them separately with the new service ID
                        if ($('#partsContainer').children().length > 0) {
                            submitParts(response.service_id);
                        } else {
                            setTimeout(function() { window.location.reload(); }, 1000);
                        }
                    } else {
                        $('#addServiceStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },

                error: function() {
                    $('#addServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });

        function submitParts(serviceId) {
            var partsData = [];
            $('#partsContainer .form-row').each(function() {
                var partName = $(this).find('input[name="part_name[]"]').val();
                var partNumber = $(this).find('input[name="part_number[]"]').val();

                if (partName && partNumber) {
                    partsData.push({ name: 'part_name[]', value: partName });
                    partsData.push({ name: 'part_number[]', value: partNumber });
                }
            });

            if (partsData.length === 0) {
                // No parts to add
                setTimeout(function() { window.location.reload(); }, 1000);
                return;
            }

            partsData.push({ name: 'service_id', value: serviceId });

            $.ajax({
                url: 'addServicePart.php',
                type: 'POST',
                data: $.param(partsData),
                success: function(response) {
                    if (response.status === 'success') {
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addServiceStatus').html('<div class="alert alert-danger">Parti non aggiunte: ' + (response.message || 'Errore sconosciuto') + '</div>');
                    }
                },
                error: function() {
                    $('#addServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto nell\'aggiunta delle parti. Riprova più tardi.</div>');
                }
            });
        }
    });
	
	// Show selected file name in input box
	$('#addServiceAttachment').on('change', function() {
		var fileName = $(this).val().split('\\').pop(); // Get the file name
		$('#fileNameDisplay').val(fileName); // Display the file name in the input box
	});

</script>
