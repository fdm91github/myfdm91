<div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Modifica Manutenzione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editServiceStatus"></div>
                <form id="editServiceForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editServiceId">
                    <input type="hidden" name="vehicle_id" id="editServiceVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="editServiceDescription">Descrizione</label>
                        <input type="text" name="description" id="editServiceDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceAmount">Costo</label>
                        <input type="number" name="amount" id="editServiceAmount" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editServiceBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceKilometers">Kilometri registrati</label>
                        <input type="number" name="registered_kilometers" id="editServiceKilometers" class="form-control" required>
                    </div>

                    <!-- Display current attachment (if any) and provide the option to upload a new one -->
                    <div id="attachmentSection">
                        <h6>Allegato corrente</h6>
                        <div id="currentAttachment">
                            <!-- Attachment info will be inserted dynamically here -->
                        </div>
                        <div class="form-check" id="deleteAttachmentContainer" style="display:none;">
                            <input type="checkbox" class="form-check-input" id="deleteAttachment" name="delete_attachment">
                            <label class="form-check-label" for="deleteAttachment">Elimina allegato</label>
                        </div>
                    </div>

                    <!-- File upload section with button and input box to display file name -->
                    <div class="form-group mt-3 d-flex align-items-center">
                        <label for="editServiceAttachment" class="btn btn-secondary" style="white-space: nowrap;">Scegli File</label>
                        <input type="file" name="attachment" id="editServiceAttachment" class="form-control-file" style="display: none;">
                        <input type="text" id="fileNameDisplay" class="form-control ml-2" placeholder="Nessun file selezionato" readonly>
                    </div>

                    <div id="partsSection">
                        <h6>Parti utilizzate</h6>
                        <div id="editPartsContainer">
                            <!-- Existing parts will be dynamically inserted here -->
                        </div>
                        <button type="button" id="editAddPartBtn" class="btn btn-secondary w-100">Aggiungi voce</button>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
		let maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

		// Show selected file name in input box
		$('#editServiceAttachment').on('change', function() {
			var fileName = $(this).val().split('\\').pop(); // Get the file name
			$('#fileNameDisplay').val(fileName); // Display the file name in the input box
		});

		// Submit the edit service form via AJAX
		$('#editServiceForm').submit(function(e) {
			e.preventDefault();

			// Check file size before submitting
			var fileInput = $('#editServiceAttachment')[0];
			if (fileInput.files.length > 0) {
				var fileSize = fileInput.files[0].size;
				if (fileSize > maxFileSize) {
					$('#editServiceStatus').html('<div class="alert alert-danger">Il file è troppo grande. La dimensione massima è 5MB.</div>');
					return; // Stop form submission
				}
			}

			// Append partsToDelete to the form data
			$('<input>').attr({
				type: 'hidden',
				name: 'parts_to_delete',
				value: partsToDelete.join(',')
			}).appendTo('#editServiceForm');

			var formData = new FormData(this); // Create FormData object for file upload

			$.ajax({
				url: 'editService.php?id=' + $('#editServiceId').val(),
				type: 'POST',
				data: formData,
				contentType: false,  // Important for file upload
				processData: false,  // Important for file upload
				success: function(response) {
					$('#editServiceStatus').html('<div class="alert alert-success">Manutenzione aggiornata con successo!</div>');
					setTimeout(function() { window.location.reload(); }, 1000);
				},
				error: function() {
					$('#editServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
				}
			});
		});

        let partsToDelete = [];

        // Show selected file name in input box
        $('#editServiceAttachment').on('change', function() {
            var fileName = $(this).val().split('\\').pop(); // Get the file name
            $('#fileNameDisplay').val(fileName); // Display the file name in the input box
        });

        // When the edit modal is shown
        $('#editServiceModal').on('show.bs.modal', function (event) {
            partsToDelete = []; // Reset the array tracking deleted parts
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var description = button.data('description');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var registered_kilometers = button.data('registered-kilometers');
            var vehicleId = button.data('vehicle-id');

            // Set the modal fields with the fetched data
            $('#editServiceId').val(id);
            $('#editServiceDescription').val(description);
            $('#editServiceAmount').val(amount);
            $('#editServiceBuyingDate').val(buying_date);
            $('#editServiceKilometers').val(registered_kilometers);
            $('#editServiceVehicleId').val(vehicleId);

            // Fetch and display existing parts
            $('#editPartsContainer').empty(); // Clear the parts container
            $.ajax({
                url: 'fetchParts.php',
                type: 'GET',
                data: { service_id: id },
                success: function(response) {
                    response.parts.forEach(function(part) {
                        addPartRow('editPartsContainer', part.part_name, part.part_number, part.id);
                    });
                }
            });

            // Fetch and display the current attachment
            $('#currentAttachment').empty(); // Clear any previous attachment info
            $('#deleteAttachmentContainer').hide(); // Hide the delete option initially
            $.ajax({
                url: 'fetchAttachment.php',  // This file will return the current attachment (if any)
                type: 'GET',
                data: { service_id: id },
                success: function(response) {
                    if (response.attachment) {
                        var attachmentHtml = `
                            <a href="${response.attachment.path}" target="_blank">${response.attachment.name}</a>`;
                        $('#currentAttachment').html(attachmentHtml);
                        $('#deleteAttachmentContainer').show(); // Show delete option only if an attachment exists
                    } else {
                        $('#currentAttachment').html('<p>Nessun allegato disponibile.</p>');
                    }
                }
            });
        });

        // Clear fields and parts when the edit modal is hidden
        $('#editServiceModal').on('hidden.bs.modal', function () {
            $('#editServiceForm')[0].reset(); // Clear form fields
            $('#editPartsContainer').empty(); // Clear parts container
            $('#fileNameDisplay').val(''); // Clear file name display
            partsToDelete = []; // Reset the partsToDelete array
        });

        // Function to dynamically add a new part row
        function addPartRow(containerId, partName = '', partNumber = '', partId = '') {
            var partRow = `
                <div class="form-row align-items-end mb-2" data-part-id="${partId}">
                    <input type="hidden" name="part_id[]" value="${partId}">
                    <div class="col">
                        <input type="text" name="part_name[]" class="form-control" placeholder="Nome prodotto" value="${partName}" required>
                    </div>
                    <div class="col">
                        <input type="text" name="part_number[]" class="form-control" placeholder="Codice prodotto" value="${partNumber}" required>
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-danger removePartBtn">&times;</button>
                    </div>
                </div>`;
            $('#' + containerId).append(partRow);
        }

        // Add a new part row when the "Add Part" button is clicked in the edit modal
        $('#editAddPartBtn').click(function() {
            addPartRow('editPartsContainer');
        });

        // Track part for deletion when the remove button is clicked
        $(document).on('click', '.removePartBtn', function() {
            var partRow = $(this).closest('.form-row');
            var partId = partRow.data('part-id');
            if (partId) {
                partsToDelete.push(partId); // Add the part ID to the deletion array
            }
            partRow.remove(); // Remove the part row from the DOM
        });

        // Submit the edit service form via AJAX
		$('#editServiceForm').submit(function(e) {
			e.preventDefault();
			$('button[type="submit"]').prop('disabled', true);
			
			// Check file size before submitting
			var fileInput = $('#editServiceAttachment')[0];
			if (fileInput.files.length > 0) {
				var fileSize = fileInput.files[0].size;
				if (fileSize > maxFileSize) {
					$('#editServiceStatus').html('<div class="alert alert-danger">Il file è troppo grande. La dimensione massima è 5MB.</div>');
					return false; // Stop form submission
				}
			}

			// Append partsToDelete to the form data
			$('<input>').attr({
				type: 'hidden',
				name: 'parts_to_delete',
				value: partsToDelete.join(',')
			}).appendTo('#editServiceForm');

			var formData = new FormData(this); // Create FormData object for file upload

			$.ajax({
				url: 'editService.php?id=' + $('#editServiceId').val(),
				type: 'POST',
				data: formData,
				contentType: false,  // Important for file upload
				processData: false,  // Important for file upload
				success: function(response) {
					$('#editServiceStatus').html('<div class="alert alert-success">Manutenzione aggiornata con successo!</div>');
					setTimeout(function() { window.location.reload(); }, 1000);
				},
				error: function() {
					$('#editServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
					$('button[type="submit"]').prop('disabled', false);
				}
			});
		});
    });
</script>
