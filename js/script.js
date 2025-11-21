// ========== MEDICINE REMINDER SYSTEM ==========
let currentReminder = null;
let popupShown = false;
let reminderCheckInterval = null;
let searchTimeout = null;

// Initialize medicine search functionality
function initMedicineSearch() {
    const medicineInput = document.getElementById('medicineNameInput');
    const searchResults = document.getElementById('searchResults');
    
    if (!medicineInput) {
        console.error('Medicine input not found');
        return;
    }
    
    medicineInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        // Hide results if query is too short
        if (query.length < 2) {
            if (searchResults) searchResults.style.display = 'none';
            return;
        }
        
        // Show loading
        if (searchResults) {
            searchResults.innerHTML = '<div class="search-loading">🔍 Searching medicine database...</div>';
            searchResults.style.display = 'block';
        }
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            searchMedicines(query);
        }, 300);
    });
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (searchResults && !medicineInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
}

// Search medicines from API
function searchMedicines(query) {
    const searchResults = document.getElementById('searchResults');
    
    fetch(`api/search_drugs.php?name=${encodeURIComponent(query)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.drugs && data.drugs.length > 0) {
                displaySearchResults(data.drugs, data.source);
            } else {
                // If no results from API, use fallback
                const fallbackDrugs = getFallbackDrugs(query);
                displaySearchResults(fallbackDrugs, 'Local Database');
            }
        })
        .catch(error => {
            console.log('API error, using fallback search');
            const fallbackDrugs = getFallbackDrugs(query);
            displaySearchResults(fallbackDrugs, 'Local Database');
        });
}

// Fallback drug data
function getFallbackDrugs(query) {
    const queryLower = query.toLowerCase();
    const allDrugs = [
        {name: 'Aspirin', synonym: 'Pain reliever', description: 'For headaches, pain, and fever'},
        {name: 'Ibuprofen', synonym: 'Advil, Motrin', description: 'NSAID for pain and inflammation'},
        {name: 'Metformin', synonym: 'Glucophage', description: 'Diabetes medication'},
        {name: 'Amoxicillin', synonym: 'Antibiotic', description: 'For bacterial infections'},
        {name: 'Lisinopril', synonym: 'Blood pressure', description: 'ACE inhibitor for hypertension'},
        {name: 'Atorvastatin', synonym: 'Lipitor', description: 'Cholesterol medication'},
        {name: 'Levothyroxine', synonym: 'Synthroid', description: 'Thyroid hormone replacement'},
        {name: 'Albuterol', synonym: 'Ventolin', description: 'Asthma inhaler'},
        {name: 'Omeprazole', synonym: 'Prilosec', description: 'Acid reducer for heartburn'},
        {name: 'Sertraline', synonym: 'Zoloft', description: 'Antidepressant (SSRI)'}
    ];
    
    return allDrugs.filter(drug => 
        drug.name.toLowerCase().includes(queryLower) || 
        drug.synonym.toLowerCase().includes(queryLower)
    );
}

// Display search results
function displaySearchResults(drugs, source) {
    const searchResults = document.getElementById('searchResults');
    const medicineInput = document.getElementById('medicineNameInput');
    const dosageInput = document.getElementById('dosageInput');
    
    if (!searchResults) return;
    
    searchResults.innerHTML = '';
    
    if (drugs.length === 0) {
        searchResults.innerHTML = '<div class="search-loading">💊 No medicines found. Try "aspirin" or "ibuprofen"</div>';
        searchResults.style.display = 'block';
        return;
    }
    
    drugs.forEach(drug => {
        const item = document.createElement('div');
        item.className = 'search-result-item';
        
        item.innerHTML = `
            <div class="medicine-name">💊 ${drug.name}</div>
            <div class="medicine-details">
                ${drug.synonym ? `<strong>Also known as:</strong> ${drug.synonym}` : ''}
            </div>
            ${drug.description ? `<div class="medicine-description">${drug.description}</div>` : ''}
            <div class="medicine-source">Source: ${source}</div>
        `;
        
        item.addEventListener('click', function() {
            // Fill medicine name
            if (medicineInput) medicineInput.value = drug.name;
            
            // Smart dosage suggestions
            let suggestedDosage = getSuggestedDosage(drug.name, drug.synonym);
            if (dosageInput && !dosageInput.value) {
                dosageInput.value = suggestedDosage;
            }
            
            searchResults.style.display = 'none';
        });
        
        searchResults.appendChild(item);
    });
    
    searchResults.style.display = 'block';
}

// Get suggested dosage based on medicine
function getSuggestedDosage(medicineName, synonyms) {
    const nameLower = medicineName.toLowerCase();
    const synonymsLower = synonyms ? synonyms.toLowerCase() : '';
    
    // Common dosage patterns
    if (nameLower.includes('aspirin')) {
        if (nameLower.includes('81') || nameLower.includes('low')) {
            return '1 tablet (81mg) - Heart protection';
        }
        return '1-2 tablets (325mg) - Pain relief';
    }
    else if (nameLower.includes('ibuprofen') || synonymsLower.includes('advil')) {
        return '1-2 tablets (200mg) - Pain and inflammation';
    }
    else if (nameLower.includes('metformin')) {
        return '1 tablet (500mg) - With meals';
    }
    else if (nameLower.includes('amoxicillin')) {
        return '1 capsule - As prescribed by doctor';
    }
    else if (nameLower.includes('vitamin') || nameLower.includes('supplement')) {
        return '1 capsule daily';
    }
    else {
        return '1 tablet - Consult dosage with doctor';
    }
}

// Set test times for immediate popup testing
function setTestTimes() {
    const now = new Date();
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    
    if (!startTime || !endTime) {
        alert('Time inputs not found');
        return;
    }
    
    // Set start time to current time
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    startTime.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Set end time to 5 minutes from now
    const end = new Date(now.getTime() + 5 * 60000);
    const endHours = String(end.getHours()).padStart(2, '0');
    const endMinutes = String(end.getMinutes()).padStart(2, '0');
    
    endTime.value = `${year}-${month}-${day}T${endHours}:${endMinutes}`;
    
    // Auto-fill medicine name if empty
    const medicineInput = document.querySelector('input[name="medicine_name"]');
    if (medicineInput && !medicineInput.value) {
        medicineInput.value = 'Test Medicine';
    }
    
    // Auto-fill dosage if empty
    const dosageInput = document.querySelector('input[name="dosage"]');
    if (dosageInput && !dosageInput.value) {
        dosageInput.value = '1 tablet';
    }
    
    alert('✅ Test times set! Start time is now, end time is 5 minutes from now. Submit the form and the popup should appear immediately.');
}

// Test function to manually show popup
function testPopup() {
    const testReminder = {
        id: 999,
        medicine_name: 'Test Medicine',
        dosage: '1 tablet',
        start_time: new Date().toISOString(),
        end_time: new Date(Date.now() + 3600000).toISOString()
    };
    showAlertPopup(testReminder);
}

// Initialize reminder system
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Medicine Tracker initialized');
    
    // Initialize medicine search
    initMedicineSearch();
    
    // Set default times using local time
    setDefaultLocalTimes();
    
    // Check for reminders immediately and then every 10 seconds
    checkReminders();
    reminderCheckInterval = setInterval(checkReminders, 10000);
    
    console.log('✅ Reminder system started - checking every 10 seconds');
});

// Set default times using local time
function setDefaultLocalTimes() {
    const now = new Date();
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    
    if (startTime && !startTime.value) {
        // Set start time to current local time
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        startTime.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    if (endTime && !endTime.value) {
        // Set end time to 1 hour after start time
        const startValue = startTime ? startTime.value : null;
        let end;
        
        if (startValue) {
            end = new Date(startValue);
            end.setHours(end.getHours() + 1);
        } else {
            end = new Date(now);
            end.setHours(end.getHours() + 1);
        }
        
        // Format for datetime-local input
        const year = end.getFullYear();
        const month = String(end.getMonth() + 1).padStart(2, '0');
        const day = String(end.getDate()).padStart(2, '0');
        const hours = String(end.getHours()).padStart(2, '0');
        const minutes = String(end.getMinutes()).padStart(2, '0');
        
        endTime.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }
}

// Check if any reminders are due
function checkReminders() {
    if (popupShown) {
        return;
    }
    
    console.log('🔍 Checking for reminders...');
    
    fetch('api/getReminders.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(reminders => {
            console.log(`✅ Found ${reminders.length} active reminders`);
            
            if (reminders.length > 0 && !popupShown) {
                const reminder = reminders[0];
                console.log('🎯 Active reminder:', reminder.medicine_name);
                showAlertPopup(reminder);
            }
        })
        .catch(error => {
            console.log('ℹ️ No active reminders found or error:', error);
        });
}

// Show alert popup
function showAlertPopup(reminder) {
    if (popupShown) {
        return;
    }
    
    currentReminder = reminder;
    popupShown = true;
    
    const alertPopup = document.getElementById('alertPopup');
    const alertMessage = document.getElementById('alertMessage');
    const popupDetails = document.getElementById('popupDetails');
    
    if (!alertPopup || !alertMessage) {
        console.error('Popup elements not found');
        return;
    }
    
    alertMessage.textContent = `Time for: ${reminder.medicine_name}`;
    
    // Format times for display
    const startTime = formatDateTimeForDisplay(reminder.start_time);
    const endTime = formatDateTimeForDisplay(reminder.end_time);
    
    // Add details to popup
    if (popupDetails) {
        popupDetails.innerHTML = `
            <strong>Dosage:</strong> ${reminder.dosage}<br>
            <strong>Start Time:</strong> ${startTime}<br>
            <strong>End Time:</strong> ${endTime}<br>
            <small>Take this medicine by ${endTime}</small>
        `;
    }
    
    alertPopup.style.display = 'flex';
    
    console.log('✅ Popup shown for:', reminder.medicine_name);
    
    // Auto-hide after 10 minutes if not closed
    setTimeout(() => {
        if (popupShown && currentReminder && currentReminder.id === reminder.id) {
            console.log('⏰ Auto-hiding popup after 10 minutes and moving to history');
            markAsTakenAndClose();
        }
    }, 600000);
}

// Format date time for display
function formatDateTimeForDisplay(dateTimeStr) {
    try {
        const date = new Date(dateTimeStr);
        if (isNaN(date.getTime())) {
            return dateTimeStr;
        }
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    } catch (e) {
        return dateTimeStr;
    }
}

// Mark as taken and close popup
function markAsTakenAndClose() {
    if (currentReminder) {
        console.log('✅ Marking as taken and closing:', currentReminder.medicine_name);
        
        // For test reminders, just hide the popup
        if (currentReminder.id === 999) {
            hideAlertPopup();
            return;
        }
        
        // For real reminders, redirect to mark as taken
        hideAlertPopup();
        window.location.href = `?move_to_history=${currentReminder.id}`;
    } else {
        hideAlertPopup();
    }
}

// Hide alert popup
function hideAlertPopup() {
    const alertPopup = document.getElementById('alertPopup');
    if (alertPopup) {
        alertPopup.style.display = 'none';
    }
    popupShown = false;
    currentReminder = null;
    console.log('✅ Popup hidden');
}

// Form validation
const medicineForm = document.getElementById('medicineForm');
if (medicineForm) {
    medicineForm.addEventListener('submit', function(e) {
        const startTime = document.querySelector('input[name="start_time"]');
        const endTime = document.querySelector('input[name="end_time"]');
        
        if (startTime && endTime && new Date(startTime.value) >= new Date(endTime.value)) {
            e.preventDefault();
            alert('❌ End time must be after start time');
            return false;
        }
        
        return true;
    });
}