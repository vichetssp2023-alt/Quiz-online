document.querySelectorAll(".btn-delete").forEach((button) => {
  button.onclick = function () {
    const userId = this.getAttribute("data-id");
    const userName = this.getAttribute("data-name");

    Swal.fire({
      title: "តើអ្នកប្រាកដទេ?",
      text: "អ្នកនឹងលុប " + userName + " ចេញពីប្រព័ន្ធ!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "បាទ, លុបវា!",
      cancelButtonText: "បោះបង់",
    }).then((result) => {
      if (result.isConfirmed) {
        // បញ្ជូនទៅកាន់ Controller
        window.location.href =
          "../../Controllers/UserController.php?action=delete&id=" + userId;
      }
    });
  };
});

// ២. មុខងារមើលរូបភាពមុនពេល Upload (Image Preview)
function previewImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      let preview = document.getElementById("previewImg");
      let placeholder = document.getElementById("previewPlaceholder");

      if (preview) {
        // បើមានរូបភាពស្រាប់ គ្រាន់តែដូរ src
        preview.src = e.target.result;
      } else if (placeholder) {
        // បើគ្មានរូបភាព (កំពុងបង្ហាញអក្សរឈ្មោះ) ត្រូវជំនួសដោយ Tag <img>
        const img = document.createElement("img");
        img.src = e.target.result;
        img.id = "previewImg";
        img.className = "rounded-circle w-100 h-100 object-fit-cover shadow-sm";
        placeholder.replaceWith(img);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ៣. មុខងារ Mark as Read ពេលចុចលើកណ្ដឹង
document.getElementById("bellDropdown").addEventListener("click", function () {
  const badge = document.getElementById("unread-badge");
  if (badge) {
    badge.style.display = "none";
  }

  // បញ្ជូន Request ទៅកាន់ mark_read.php ដើម្បី Update ក្នុង Database
  fetch("mark_read.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        console.log("Notifications marked as read");
      }
    })
    .catch((err) => console.error("Error:", err));
});

// Track the state of the table
let isExpanded = false;

document.getElementById("btnToggleView").addEventListener("click", function () {
  const rows = document.querySelectorAll(".user-row");
  isExpanded = !isExpanded; // Toggle the state

  if (isExpanded) {
    // Show All Rows
    rows.forEach((row) => row.classList.remove("d-none"));
    this.textContent = "បង្ហាញតិចតួច"; // Change text to "Show Less"
  } else {
    // Show only first 5 (Default)
    rows.forEach((row, index) => {
      if (index >= 5) {
        row.classList.add("d-none");
      } else {
        row.classList.remove("d-none");
      }
    });
    this.textContent = "មើលទាំងអស់"; // Change text back to "View All"
  }
});

// Updated Search Logic to handle the toggle button visibility
document
  .getElementById("userSearchInput")
  .addEventListener("keyup", function () {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll(".user-row");
    const toggleBtn = document.getElementById("btnToggleView");

    rows.forEach((row) => {
      const text = row.cells[0].textContent.toLowerCase();
      if (text.includes(filter)) {
        row.classList.remove("d-none");
      } else {
        row.classList.add("d-none");
      }
    });

    // Hide the toggle button if the user is searching,
    // because we need to show all matches regardless of the "Show Less" limit.
    if (filter !== "") {
      toggleBtn.style.visibility = "hidden";
    } else {
      toggleBtn.style.visibility = "visible";
      // Reset to the correct state if search is cleared
      if (!isExpanded) {
        rows.forEach((row, index) => {
          if (index >= 5) row.classList.add("d-none");
        });
      }
    }
  });

const themeToggle = document.getElementById("theme-toggle");
const themeIcon = document.getElementById("theme-icon");
const themeText = document.getElementById("theme-text");
const bodyElement = document.body; // Using body is usually more reliable for BS5

(function () {
  // Check saved theme immediately to prevent "white flash"
  const savedTheme = localStorage.getItem("quiz-theme") || "light";
  document.documentElement.setAttribute("data-theme", savedTheme);
})();

document.addEventListener("DOMContentLoaded", () => {
  const themeBtn = document.getElementById("theme-toggle");

  // Function to update the Icons and Text
  function updateUI(theme) {
    const themeText = document.getElementById("theme-text");
    const themeIcon = document.getElementById("theme-icon");

    if (!themeText || !themeIcon) return;

    if (theme === "dark") {
      themeText.innerText = "ប្តូរទៅ Light Mode";
      themeIcon.classList.replace("fa-moon", "fa-sun");
    } else {
      themeText.innerText = "ប្តូរទៅ Dark Mode";
      themeIcon.classList.replace("fa-sun", "fa-moon");
    }
  }

  // Initialize UI on page load
  const currentSavedTheme = localStorage.getItem("quiz-theme") || "light";
  updateUI(currentSavedTheme);

  // Toggle Click Event
  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      const currentTheme = document.documentElement.getAttribute("data-theme");
      const newTheme = currentTheme === "dark" ? "light" : "dark";

      // 1. Update the <html> attribute
      document.documentElement.setAttribute("data-theme", newTheme);
      // 2. Save to localStorage
      localStorage.setItem("quiz-theme", newTheme);
      // 3. Update the button text/icon
      updateUI(newTheme);
    });
  }
});
