<!-- Modale per la modifica di un veicolo -->
<div class="modal fade" id="editVehicleModal" tabindex="-1" aria-labelledby="editVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVehicleModalLabel">Modifica veicolo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="editVehicleStatus"></div>
                <form id="editVehicleForm">
                    <input type="hidden" name="id" id="editVehicleId">
                    <div class="form-group">
                        <label for="editVehicleDescription">Descrizione</label>
                        <input type="text" name="description" id="editVehicleDescription" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editVehicleBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="editVehicleBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editVehicleRegistrationDate">Data di immatricolazione</label>
                        <input type="date" name="registration_date" id="editVehicleRegistrationDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editVehiclePlateNumber">Targa</label>
                        <input type="text" name="plate_number" id="editVehiclePlateNumber" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editVehicleChassisNumber">Nr. di telaio</label>
                        <input type="text" name="chassis_number" id="editVehicleChassisNumber" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editVehicleTaxMonth">Mese di scadenza del bollo</label>
                        <input type="text" name="tax_month" id="editVehicleTaxMonth" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aggiorna</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function validatePlateNumber(plateNumber) {
        // Regular expression for 6-character plates (e.g., A12345)
        var sixCharPattern = /^[A-Z][A-Z0-9]{5}$/;
        
        // Regular expression for 7-character plates (e.g., AB123CD for cars, AB12345 for motorcycles)
        var sevenCharPatternCar = /^[A-Z]{2}\d{3}[A-Z]{2}$/;
        var sevenCharPatternMotorcycle = /^[A-Z]{2}\d{5}$/;
        
        if (plateNumber.length === 6) {
            return sixCharPattern.test(plateNumber);
        } else if (plateNumber.length === 7) {
            return sevenCharPatternCar.test(plateNumber) || sevenCharPatternMotorcycle.test(plateNumber);
        } else {
            return false;
        }
    }

    $(document).ready(function() {
        $('#editVehicleModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var description = button.data('description');
            var buyingDate = button.data('buying-date');
            var registrationDate = button.data('registration-date');
            var plateNumber = button.data('plate-number');
            var chassisNumber = button.data('chassis-number');
            var taxMonth = button.data('tax-month');

            $('#editVehicleId').val(id);
            $('#editVehicleDescription').val(description);
            $('#editVehicleBuyingDate').val(buyingDate);
            $('#editVehicleRegistrationDate').val(registrationDate);
            $('#editVehiclePlateNumber').val(plateNumber);
            $('#editVehicleChassisNumber').val(chassisNumber);
            $('#editVehicleTaxMonth').val(taxMonth);
        });

        $('#editVehicleForm').submit(function(e) {
            e.preventDefault();

            var plateNumber = $('#editVehiclePlateNumber').val().toUpperCase();

            if (!validatePlateNumber(plateNumber)) {
                $('#editVehicleStatus').html('<div class="alert alert-danger">Il numero di targa non è valido. Assicurati che sia nel formato corretto.</div>');
                return;
            }

            $.ajax({
                url: 'editVehicle.php?id=' + $('#editVehicleId').val(),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    $('#editVehicleStatus').html('<div class="alert alert-success">Veicolo aggiornato con successo!</div>');
                    setTimeout(function() { window.location.reload(); }, 1000);
                },
                error: function() {
                    $('#editVehicleStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
                }
            });
        });
    });
</script>
