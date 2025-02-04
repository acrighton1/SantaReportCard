document.addEventListener("DOMContentLoaded", () => {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", function (e) {
        e.preventDefault()
        document.querySelector(this.getAttribute("href")).scrollIntoView({
          behavior: "smooth",
        })
      })
    })
  
    // Simple form submission (you'll need to implement the actual submission logic)
    const ctaButtons = document.querySelectorAll(".btn-primary")
    ctaButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault()
        alert("Thank you for your interest! We'll notify you when we launch.")
      })
    })
  
    // FAQ accordion functionality
//     const faqItems = document.querySelectorAll(".faq-item")
//     faqItems.forEach((item) => {
//       const question = item.querySelector("h3")
//       const answer = item.querySelector("p")
  
//       question.addEventListener("click", () => {
//         answer.style.display = answer.style.display === "none" ? "block" : "none"
//       })
//     })
//   })
  
//   document.getElementById("hamburger").addEventListener("click", function () {
//     document.getElementById("nav-links").classList.toggle("show");
// });
  
//Child Dropdown Select for Report Card
jQuery(document).ready(function($) {
  $('#child-select').change(function() {
      var kidId = $(this).val();
      if (!kidId) return;

      $.ajax({
          type: 'POST',
          url: ajax_object.ajax_url,
          data: {
              action: 'get_report_card',
              kid_id: kidId
          },
          beforeSend: function() {
              $('.report-card-display').html('<p>Loading...</p>');
          },
          success: function(response) {
              if (response.success) {
                  $('.report-card-display').html(response.data);
              } else {
                  $('.report-card-display').html('<p>No report card available.</p>');
              }
          }
        });
      });
  });
});
