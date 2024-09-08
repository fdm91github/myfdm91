<!-- Modale per la modifica di un'assicurazione -->
<div class="modal fade" id="editInsuranceModal" tabindex="-1" role="dialog" aria-labelledby="editInsuranceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInsuranceModalLabel">Modifica Assicurazione</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="editInsuranceStatus"></div>
                <form id="editInsuranceForm">
                    <input type="hidden" name="id" id="editInsuranceId">
                    <input type="hidden" name="vehicle_id" id="editInsuranceVehicleId"> <!-- Hidden vehicle_id -->
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
                    <button type="submit" class="btn btn-primary btn-block">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#editInsuranceModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var id = button.data('id');
            var company = button.data('company');
            var amount = button.data('amount');
            var buying_date = button.data('buying-date');
            var vehicleId = button.data('vehicle-id');  // Ensure vehicle_id is passed

            $('#editInsuranceId').val(id);
            $('#editInsuranceCompany').val(company);
            $('#editInsuranceAmount').val(amount);
            $('#editInsuranceBuyingDate').val(buying_date);
            $('#editInsuranceVehicleId').val(vehicleId);  // Set the hidden vehicle_id field
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
