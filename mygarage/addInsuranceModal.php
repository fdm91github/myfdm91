<!-- Modale per l'aggiunta di una voce in Assicurazioni -->
<div class="modal fade" id="addInsuranceModal" tabindex="-1" aria-labelledby="addInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInsuranceModalLabel">Aggiungi un'assicurazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="addInsuranceStatus"></div>
                <form id="addInsuranceForm">
                    <input type="hidden" name="vehicle_id" id="addInsuranceVehicleId">
                    <div class="mb-3">
                        <label for="addInsuranceCompany" class="form-label">Compagnia assicurativa</label>
                        <input type="text" name="company" id="addInsuranceCompany" class="form-control" placeholder="Compagnia assicurativa" required>
                    </div>
                    <div class="mb-3">
                        <label for="addInsuranceAmount" class="form-label">Costo</label>
                        <input type="number" name="amount" id="addInsuranceAmount" class="form-control" step="0.01" placeholder="Costo" required>
                    </div>
                    <div class="mb-3">
                        <label for="addInsuranceBuyingDate" class="form-label">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addInsuranceBuyingDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="addInsuranceEffectiveDate" class="form-label">Data di decorrenza</label>
                        <input type="date" name="effective_date" id="addInsuranceEffectiveDate" class="form-control" required>
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
        $('#addInsuranceModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var vehicleId = button.data('vehicle-id'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#addInsuranceVehicleId').val(vehicleId);
        });

        $('#addInsuranceForm').submit(function(e) {
            e.preventDefault();
            $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: 'addInsurance.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $('#addInsuranceStatus').html('<div class="alert alert-success">Assicurazione aggiunta con successo!</div>');
                        $('#addInsuranceForm')[0].reset();
                        setTimeout(function() { window.location.reload(); }, 1000);
                    } else {
                        $('#addInsuranceStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                    $('button[type="submit"]').prop('disabled', false);
                },

                error: function() {
                    $('#addInsuranceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                    $('button[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
</script>
