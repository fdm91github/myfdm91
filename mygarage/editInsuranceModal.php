<!-- Modale per la modifica di un'assicurazione -->
<div class="modal fade" id="editInsuranceModal" tabindex="-1" aria-labelledby="editInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInsuranceModalLabel">Modifica Assicurazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editInsuranceStatus"></div>
                <form id="editInsuranceForm">
                    <input type="hidden" name="id" id="editInsuranceId">
                    <input type="hidden" name="vehicle_id" id="editInsuranceVehicleId">
                    <div class="form-group">
                        <label for="editInsuranceCompany">Compagnia assicurativa</label>
                        <input type="text" name="company" id="editInsuranceCompany" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editInsuranceAmount">Costo</label>
                        <input type="number" name="amount" id="editInsuranceAmount" step="0.01" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editInsuranceBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editInsuranceBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editInsuranceEffectiiveDate">Decorrenza</label>
                        <input type="date" name="effective_date" id="editInsuranceEffectiveDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editInsuranceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var company = button.data('company');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var effective_date = button.data('effective-date');
            var vehicleId = button.data('vehicle-id');

            $('#editInsuranceId').val(id);
            $('#editInsuranceCompany').val(company);
            $('#editInsuranceAmount').val(amount);
            $('#editInsuranceBuyingDate').val(buying_date);
            $('#editInsuranceEffectiveDate').val(effective_date);
            $('#editInsuranceVehicleId').val(vehicleId);
        });

        $('#editInsuranceForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: 'editInsurance.php?id=' + $('#editInsuranceId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editInsuranceStatus').html('<div class="alert alert-success">Assicurazione aggiornata con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editInsuranceStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
