<!-- Modale per l'aggiunta di un veicolo -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" role="dialog" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVehicleModalLabel">Aggiungi un veicolo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="addVehicleStatus"></div>
                <form id="addVehicleForm">
                    <div class="form-group">
                        <label for="addVehicleDescription">Descrizione</label>
                        <input type="text" name="description" id="addVehicleDescription" class="form-control" placeholder="Descrizione" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehicleBuyingDate">Data di acquisto</label>
                        <input type="date" name="buying_date" id="addVehicleBuyingDate" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehiclePlateNumber">Targa</label>
                        <input type="text" name="plate" id="addVehiclePlateNumber" class="form-control" placeholder="Targa" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehicleChassisNumber">Nr. di telaio</label>
                        <input type="text" name="chassis_number" id="addVehicleChassisNumber" class="form-control" placeholder="Nr. di telaio" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehicleTaxExpiryMonth">Mese di scadenza del bollo</label>
                        <input type="text" name="tax_expiry_month" id="addVehicleTaxExpiryMonth" class="form-control" placeholder="Mese di scadenza del bollo" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehicleInspectionExpiryMonth">Mese di scadenza della revisione</label>
                        <input type="text" name="inspection_expiry_month" id="addVehicleInspectionExpiryMonth" class="form-control" placeholder="Mese di scadenza della revisione" required>
                    </div>
                    <div class="form-group">
                        <label for="addVehicleInsuranceExpirationDate">Data di scadenza dell'assicurazione</label>
                        <input type="date" name="insurance_expiry_date" id="addVehicleInsuranceExpirationDate" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Aggiungi</button>
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
		$('#addVehicleForm').submit(function(e) {
		e.preventDefault();
		$('button[type="submit"]').prop('disabled', true);
		
		var plateNumber = $('#addVehiclePlateNumber').val().toUpperCase();

            if (!validatePlateNumber(plateNumber)) {
                $('#addVehicleStatus').html('<div class="alert alert-danger">Il numero di targa non è valido. Assicurati che sia nel formato corretto.</div>');
                $('button[type="submit"]').prop('disabled', false);
                return;
            }
			
			$.ajax({
				url: 'addVehicle.php',
				type: 'POST',
				data: $(this).serialize(),
				success: function(response) {
				if(response.status === 'success') {
			$('#addVehicleStatus').html('<div class="alert alert-success">Veicolo aggiunto con successo!</div>');
			$('#addVehicleForm')[0].reset();
			setTimeout(function() { window.location.reload(); }, 1000);
			} else {
			$('#addVehicleStatus').html('<div class="alert alert-danger">' + response.message + '</div>');
			}
			$('button[type="submit"]').prop('disabled', false);
		},

		error: function() {
			$('#addVehicleStatus').html('<div class="alert alert-danger">Qualcosa è andato storto. Riprova più tardi.</div>');
			$('button[type="submit"]').prop('disabled', false); // Re-enable submit button
				}
			});
		});
    });
</script>
