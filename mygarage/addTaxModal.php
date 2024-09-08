<!-- Modale per l'aggiunta di una voce in Bolli -->
<div class="modal fade" id="addTaxModal" tabindex="-1" role="dialog" aria-labelledby="addTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTaxModalLabel">Aggiungi un bollo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addTaxStatus"></div>
                <form id="addTaxForm">
                    <input type="hidden" name="vehicle_id" id="addTaxVehicleId">
                    <div class="form-group">
                        <label for="addTaxAmount">Costo</label>
                        <input type="number" name="amount" id="addTaxAmount" class="form-control" step="0.01" placeholder="Costo" required>
                    </div>
                    <div class="form-group">
                        <label for="addTaxBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addTaxBuyingDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiungi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Set the vehicle_id in the hidden field when the modal is shown
        $('#addTaxModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var vehicleId = button.data('vehicle-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#addTaxVehicleId').val(vehicleId);
        });

        $('#addTaxForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: 'addTax.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $('#addTaxStatus').html('<div class="alert alert-success">Bollo aggiunto con successo!</div>');
                        $('#addTaxForm')[0].reset();
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addTaxStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },

                error: function() {
                    $('#addTaxStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>
