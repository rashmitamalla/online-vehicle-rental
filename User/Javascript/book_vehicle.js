document.addEventListener("DOMContentLoaded", () => {
  const addBtn = document.getElementById("add-to-favorite");
  const removeBtn = document.getElementById("remove-from-favorite");

  addBtn?.addEventListener("click", function () {
    const vehicleId = this.dataset.vehicleId;
    fetch("add_to_favorite.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "vehicle_id=" + encodeURIComponent(vehicleId)
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if (data.status === "success") {
          addBtn.style.display = "none";
          removeBtn.style.display = "inline-block";
        }
      })
      .catch(error => {
        console.error("Fetch error (add):", error);
        alert("Error: Could not contact server.");
      });
  });

  removeBtn?.addEventListener("click", function () {
    const vehicleId = this.dataset.vehicleId;
    fetch("remove_from_favorite.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "vehicle_id=" + encodeURIComponent(vehicleId)
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        if (data.status === "success") {
          removeBtn.style.display = "none";
          addBtn.style.display = "inline-block";
        }
      })
      .catch(error => {
        console.error("Fetch error (remove):", error);
        alert("Error: Could not contact server.");
      });
  });
});

function calculateTotal() {
  const pickupDateStr = document.getElementById("pickup_date").value;
  const pickupTimeStr = document.getElementById("pickup_time").value;
  const returnDateStr = document.getElementById("return_date").value;
  const returnTimeStr = document.getElementById("return_time").value;
  const vehiclePrice = parseFloat(document.getElementById("vehicle_price").value); // daily price

  if (!pickupDateStr || !pickupTimeStr || !returnDateStr || !returnTimeStr || isNaN(vehiclePrice)) {
    return;
  }

  const pickup = new Date(`${pickupDateStr}T${pickupTimeStr}`);
  const ret = new Date(`${returnDateStr}T${returnTimeStr}`);

  if (ret <= pickup) {
    document.getElementById("total_price").value = "0.00";
    return;
  }

  const diffMs = ret - pickup;
  const diffHours = diffMs / (1000 * 60 * 60);

  if (diffHours < 2) {
    document.getElementById("total_price").value = "0.00";
    return;
  }

  let total;
  if (diffHours <= 24) {
    total = vehiclePrice; // minimum charge = 1 day
    document.getElementById("hidden_booking_type").value = "hourly(min-1day)";
  } else {
    const fullDays = Math.floor(diffHours / 24);
    const remainingHours = diffHours % 24;
    const hourlyRate = vehiclePrice / 24;
    total = (fullDays * vehiclePrice) + (remainingHours * hourlyRate);
    document.getElementById("hidden_booking_type").value = remainingHours > 0 ? "daily+hourly" : "daily";
  }

  const roundedTotal = Math.round(total / 10) * 10;
document.getElementById("total_price").value = roundedTotal.toFixed(2);
}


function validateForm() {

 
  const pickup = new Date(document.getElementById("pickup_date").value + 'T' + document.getElementById("pickup_time").value);
  const ret = new Date(document.getElementById("return_date").value + 'T' + document.getElementById("return_time").value);
  const diffHours = (ret - pickup) / (1000 * 60 * 60);

  if (ret <= pickup) {
    alert("Return date and time must be after pickup.");
    return false;
  }

  if (diffHours < 2) {
    alert("Minimum booking time is 2 hours.");
    return false;
  }

  const phoneNumber = document.getElementById("number").value;
  const phoneNumberPattern = /^(97|98)\d{8}$/;
  const fullName = document.getElementById("fullname").value;
  const namePattern = /^[A-Z][a-z]+(?: [A-Z][a-z]+)*$/;

  if (!namePattern.test(fullName)) {
    alert("Invalid full name. Each name should start with a capital letter (e.g., John Doe).");
    return false;
  }

  if (!phoneNumberPattern.test(phoneNumber)) {
    alert("Phone number must start with '97' or '98' and be exactly 10 digits.");
    return false;
  }

 
  return true;
}

function updateReturnDateMin() {
  const pickupDate = document.getElementById("pickup_date").value;
  if (pickupDate) {
    document.getElementById("return_date").setAttribute("min", pickupDate);
  }
}


 
