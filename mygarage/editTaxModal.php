<!-- Modale per la modifica di un bollo -->
<div class="modal fade" id="editTaxModal" tabindex="-1" role="dialog" aria-labelledby="editTaxModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaxModalLabel">Modifica Bollo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editTaxStatus"></div>
                <form id="editTaxForm">
                    <input type="hidden" name="id" id="editTaxId">
                    <input type="hidden" name="vehicle_id" id="editTaxVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="editTaxAmount">Costo</label>
                        <input type="number" name="amount" id="editTaxAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editTaxBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editTaxBuyingDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editTaxModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var vehicleId = button.data('vehicle-id');  // Ensure vehicle_id is passed

            $('#editTaxId').val(id);
            $('#editTaxAmount').val(amount);
            $('#editTaxBuyingDate').val(buying_date);
            $('#editTaxVehicleId').val(vehicleId);  // Set the hidden vehicle_id field
        });

        $('#editTaxForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editTax.php?id=' + $('#editTaxId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editTaxStatus').html('<div class="alert alert-success">Bollo aggiornato con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editTaxStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
