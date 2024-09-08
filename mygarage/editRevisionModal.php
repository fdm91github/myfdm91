<!-- Modale per la modifica di una revisione -->
<div class="modal fade" id="editRevisionModal" tabindex="-1" role="dialog" aria-labelledby="editRevisionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRevisionModalLabel">Modifica Revisione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editRevisionStatus"></div>
                <form id="editRevisionForm">
                    <input type="hidden" name="id" id="editRevisionId">
                    <input type="hidden" name="vehicle_id" id="editRevisionVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="editRevisionAmount">Costo</label>
                        <input type="number" name="amount" id="editRevisionAmount" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editRevisionBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editRevisionBuyingDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editRevisionModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var vehicleId = button.data('vehicle-id');  // Ensure vehicle_id is passed

            $('#editRevisionId').val(id);
            $('#editRevisionAmount').val(amount);
            $('#editRevisionBuyingDate').val(buying_date);
            $('#editRevisionVehicleId').val(vehicleId);  // Set the hidden vehicle_id field
        });

        $('#editRevisionForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editRevision.php?id=' + $('#editRevisionId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editRevisionStatus').html('<div class="alert alert-success">Revisione aggiornata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editRevisionStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
