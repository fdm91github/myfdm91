<!-- Modale per la modifica di una manutenzione -->
<div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">Modifica Manutenzione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editServiceStatus"></div>
                <form id="editServiceForm">
                    <input type="hidden" name="id" id="editServiceId">
                    <input type="hidden" name="vehicle_id" id="editServiceVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="editServiceDescription">Descrizione</label>
                        <input type="text" name="description" id="editServiceDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceAmount">Costo</label>
                        <input type="number" name="amount" id="editServiceAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editServiceBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceKilometers">Kilometri registrati</label>
                        <input type="number" name="registered_kilometers" id="editServiceKilometers" class="form-control" required>
                    </div>
                    <div id="partsSection">
                        <h6>Parti utilizzate</h6>
                        <div id="editPartsContainer">
                            <!-- Existing parts will be dynamically inserted here -->
                        </div>
                        <button type="button" id="editAddPartBtn" class="btn btn-secondary btn-block">Aggiungi voce</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let partsToDelete = [];

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
        });

        // Clear fields and parts when the edit modal is hidden
        $('#editServiceModal').on('hidden.bs.modal', function () {
            $('#editServiceForm')[0].reset(); // Clear form fields
            $('#editPartsContainer').empty(); // Clear parts container
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

            // Append partsToDelete to the form data
            $('<input>').attr({
                type: 'hidden',
                name: 'parts_to_delete',
                value: partsToDelete.join(',')
            }).appendTo('#editServiceForm');

            $.ajax({
                url: 'editService.php?id=' + $('#editServiceId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editServiceStatus').html('<div class="alert alert-success">Manutenzione aggiornata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editServiceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
