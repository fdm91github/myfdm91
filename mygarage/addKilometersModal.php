<!-- Modale per l'aggiunta di una voce in Percorrenza -->
<div class="modal fade" id="addKilometersModal" tabindex="-1" role="dialog" aria-labelledby="addKilometersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKilometersModalLabel">Registra una percorrenza</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addKilometersStatus"></div>
                <form id="addKilometersForm">
                    <input type="hidden" name="vehicle_id" id="addKilometersVehicleId">
                    <div class="form-group">
                        <label for="addKilometersAmount">KM registrati</label>
                        <input type="number" name="amount" id="addKilometersAmount" class="form-control" placeholder="KM" required>
                    </div>
                    <div class="form-group">
                        <label for="addKilometersDate">Data di registrazione</label>
                        <input type="date" name="date" id="addKilometersDate" class="form-control" required>
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
        $('#addKilometersModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var vehicleId = button.data('vehicle-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#addKilometersVehicleId').val(vehicleId);
        });

        $('#addKilometersForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: 'addKilometers.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $('#addKilometersStatus').html('<div class="alert alert-success">Percorrenza aggiunta con successo!</div>');
                        $('#addKilometersForm')[0].reset();
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addKilometersStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },

                error: function() {
                    $('#addKilometersStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>
