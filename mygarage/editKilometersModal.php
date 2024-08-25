<!-- Modale per la modifica di una percorrenza -->
<div class="modal fade" id="editKilometersModal" tabindex="-1" role="dialog" aria-labelledby="editKilometersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKilometersModalLabel">Modifica Percorrenza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editKilometersStatus"></div>
                <form id="editKilometersForm">
                    <input type="hidden" name="id" id="editKilometersId">
                    <input type="hidden" name="vehicle_id" id="editKilometersVehicleId"> <!-- Hidden vehicle_id -->
                    <div class="form-group">
                        <label for="editKilometersAmount">KM registrati</label>
                        <input type="number" name="amount" id="editKilometersAmount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editKilometersDate">Data di registrazione</label>
                        <input type="date" name="date" id="editKilometersDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editKilometersModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var amount = button.data('amount');
            var date = button.data('date');
            var vehicleId = button.data('vehicle-id');  // Ensure vehicle_id is passed

            $('#editKilometersId').val(id);
            $('#editKilometersAmount').val(amount);
            $('#editKilometersDate').val(date);
            $('#editKilometersVehicleId').val(vehicleId);  // Set the hidden vehicle_id field
        });

        $('#editKilometersForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editKilometers.php?id=' + $('#editKilometersId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editKilometersStatus').html('<div class="alert alert-success">Percorrenza aggiornata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editKilometersStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
