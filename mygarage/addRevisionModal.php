<!-- Modale per l'aggiunta di una voce in Revisioni -->
<div class="modal fade" id="addRevisionModal" tabindex="-1" aria-labelledby="addRevisionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRevisionModalLabel">Aggiungi una revisione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addRevisionStatus"></div>
                <form id="addRevisionForm">
                    <input type="hidden" name="vehicle_id" id="addRevisionVehicleId">
                    <div class="form-group">
                        <label for="addRevisionAmount">Costo</label>
                        <input type="number" name="amount" id="addRevisionAmount" class="form-control" step="0.01" placeholder="Costo" required>
                    </div>
                    <div class="form-group">
                        <label for="addRevisionBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addRevisionBuyingDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set the vehicle_id in the hidden field when the modal is shown
        $('#addRevisionModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var vehicleId = button.data('vehicle-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#addRevisionVehicleId').val(vehicleId);
        });

        $('#addRevisionForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: 'addRevision.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $('#addRevisionStatus').html('<div class="alert alert-success">Revisione aggiunta con successo!</div>');
                        $('#addRevisionForm')[0].reset();
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addRevisionStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },

                error: function() {
                    $('#addRevisionStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>
