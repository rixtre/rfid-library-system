// Application state
let currentUser = null;
let appData = {
    users: [],
    transactions: [],
    penalties: []
};

// DOM Elements
const screens = {
    login: document.getElementById('loginScreen'),
    admin: document.getElementById('adminDashboard'),
    librarian: document.getElementById('librarianDashboard'),
    student: document.getElementById('studentDashboard'),
    addLibrarian: document.getElementById('addLibrarianScreen'),
    addStudent: document.getElementById('addStudentScreen'),
    manageUsers: document.getElementById('manageUsersScreen'),
    borrowBook: document.getElementById('borrowBookScreen'),
    returnBook: document.getElementById('returnBookScreen'),
    changePassword: document.getElementById('changePasswordScreen'),
    viewTransactions: document.getElementById('viewTransactionsScreen'),
    viewLogs: document.getElementById('viewLogsScreen')
};

const modals = {
    resetPassword: document.getElementById('resetPasswordModal'),
    deleteUser: document.getElementById('deleteUserModal'),
    success: document.getElementById('successModal'),
    error: document.getElementById('errorModal')
};

// User to be deleted (temporary storage)
let userToDelete = null;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    loadData();
    setupEventListeners();
    showScreen('login');
});

// Load data from JSON
function loadData() {
    // Try to load from localStorage first
    const savedData = localStorage.getItem('libraryData');
    if (savedData) {
        appData = JSON.parse(savedData);
    } else {
        // Initialize with default data if none exists
        initializeDefaultData();
    }
}

// Save data to localStorage
function saveData() {
    localStorage.setItem('libraryData', JSON.stringify(appData));
}

// Initialize with default data
function initializeDefaultData() {
    appData = {
        users: [
            {
                id: 1,
                username: 'admin',
                password: 'admin123',
                role: 'admin',
                rfid_tag: '3235673260',
                name: 'System Administrator',
                gender: 'male',
                active: true
            },
            {
                id: 2,
                username: 'librarian1',
                password: 'lib123',
                role: 'librarian',
                rfid_tag: '1122334455',
                name: 'Jane Librarian',
                gender: 'female',
                active: true
            },
            {
                id: 3,
                username: 'juancruz',
                password: 'student123',
                role: 'student',
                rfid_tag: '9988776655',
                name: 'Juan Cruz',
                gender: 'male',
                active: true
            }
        ],
        transactions: [],
        penalties: []
    };
    saveData();
}

// Setup all event listeners
function setupEventListeners() {
    // Login form
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    
    // Logout buttons
    document.getElementById('adminLogout').addEventListener('click', logout);
    document.getElementById('librarianLogout').addEventListener('click', logout);
    document.getElementById('studentLogout').addEventListener('click', logout);
    
    // Admin dashboard buttons
    document.getElementById('addLibrarianBtn').addEventListener('click', () => showScreen('addLibrarian'));
    document.getElementById('resetPasswordBtn').addEventListener('click', showResetPasswordModal);
    document.getElementById('manageUsersBtn').addEventListener('click', () => {
        loadUsersTable();
        showScreen('manageUsers');
    });
    document.getElementById('viewTransactionsBtn').addEventListener('click', () => {
        loadTransactionsTable();
        showScreen('viewTransactions');
    });
    document.getElementById('viewLogsBtn').addEventListener('click', () => {
        loadLogsTable();
        showScreen('viewLogs');
    });
    
    // Librarian dashboard buttons
    document.getElementById('addStudentBtn').addEventListener('click', () => showScreen('addStudent'));
    document.getElementById('borrowBookBtn').addEventListener('click', () => showScreen('borrowBook'));
    document.getElementById('returnBookBtn').addEventListener('click', () => showScreen('returnBook'));
    document.getElementById('changePasswordBtn').addEventListener('click', () => showScreen('changePassword'));
    document.getElementById('viewLibrarianTransactionsBtn').addEventListener('click', () => {
        loadTransactionsTable();
        showScreen('viewTransactions');
    });
    
    // Back buttons
    document.getElementById('backFromAddLibrarian').addEventListener('click', () => showScreen('admin'));
    document.getElementById('backFromAddStudent').addEventListener('click', () => showScreen('librarian'));
    document.getElementById('backFromManageUsers').addEventListener('click', () => showScreen('admin'));
    document.getElementById('backFromBorrow').addEventListener('click', () => showScreen('librarian'));
    document.getElementById('backFromReturn').addEventListener('click', () => showScreen('librarian'));
    document.getElementById('backFromChangePassword').addEventListener('click', () => showScreen('librarian'));
    document.getElementById('backFromTransactions').addEventListener('click', () => {
        if (currentUser.role === 'admin') showScreen('admin');
        else showScreen('librarian');
    });
    document.getElementById('backFromLogs').addEventListener('click', () => showScreen('admin'));
    
    // Form submissions
    document.getElementById('addLibrarianForm').addEventListener('submit', handleAddLibrarian);
    document.getElementById('addStudentForm').addEventListener('submit', handleAddStudent);
    document.getElementById('borrowBookForm').addEventListener('submit', handleBorrowBook);
    document.getElementById('returnBookForm').addEventListener('submit', handleReturnBook);
    document.getElementById('changePasswordForm').addEventListener('submit', handleChangePassword);
    
    // Filter and search
    document.getElementById('roleFilter').addEventListener('change', loadUsersTable);
    document.getElementById('userSearch').addEventListener('input', loadUsersTable);
    
    // Modal buttons
    document.getElementById('cancelReset').addEventListener('click', () => hideModal('resetPassword'));
    document.getElementById('confirmReset').addEventListener('click', handleResetPassword);
    document.getElementById('cancelDelete').addEventListener('click', () => hideModal('deleteUser'));
    document.getElementById('confirmDelete').addEventListener('click', handleDeleteUser);
    document.getElementById('successOk').addEventListener('click', () => hideModal('success'));
    document.getElementById('errorOk').addEventListener('click', () => hideModal('error'));
}

// Show a specific screen
function showScreen(screenName) {
    // Hide all screens
    Object.values(screens).forEach(screen => {
        screen.classList.remove('active');
    });
    
    // Show the requested screen
    if (screens[screenName]) {
        screens[screenName].classList.add('active');
        
        // Update screen-specific content
        if (screenName === 'student') {
            loadStudentData();
        } else if (screenName === 'admin') {
            loadLibrarianSelect();
        }
    }
}

// Show a modal
function showModal(modalName) {
    if (modals[modalName]) {
        modals[modalName].classList.add('active');
    }
}

// Hide a modal
function hideModal(modalName) {
    if (modals[modalName]) {
        modals[modalName].classList.remove('active');
    }
}

// Show success message
function showSuccess(message) {
    document.getElementById('successMessage').textContent = message;
    showModal('success');
}

// Show error message
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    showModal('error');
}

// Handle login
function handleLogin(e) {
    e.preventDefault();
    
    const rfid = document.getElementById('rfidInput').value;
    const password = document.getElementById('passwordInput').value;
    
    // Find user by RFID or password
    const user = appData.users.find(u => 
        u.active && ((rfid && u.rfid_tag === rfid) || 
        (password && u.password === password))
    );
    
    if (user) {
        currentUser = user;
        
        // Update dashboard header
        const header = document.querySelector(`#${user.role}Dashboard .header-content h1`);
        if (header) {
            header.textContent = `Welcome, ${user.name || user.username}`;
        }
        
        // Redirect to appropriate dashboard
        if (user.role === 'admin') {
            showScreen('admin');
        } else if (user.role === 'librarian') {
            showScreen('librarian');
        } else if (user.role === 'student') {
            showScreen('student');
        }
        
        // Clear form
        document.getElementById('loginForm').reset();
    } else {
        showError('Invalid RFID or password');
    }
}

// Handle logout
function logout() {
    currentUser = null;
    showScreen('login');
}

// Handle add librarian
function handleAddLibrarian(e) {
    e.preventDefault();
    
    const employeeId = document.getElementById('employeeId').value;
    const name = document.getElementById('librarianName').value;
    const gender = document.getElementById('librarianGender').value;
    const rfid = document.getElementById('librarianRfid').value;
    
    // Check for duplicates
    const duplicate = appData.users.find(u => 
        u.active && (u.username === employeeId || u.rfid_tag === rfid)
    );
    
    if (duplicate) {
        showError('Librarian with this Employee ID or RFID already exists');
        return;
    }
    
    // Add new librarian
    const newLibrarian = {
        id: Date.now(), // Simple ID generation
        username: employeeId,
        password: 'lib123', // Default password
        role: 'librarian',
        rfid_tag: rfid,
        name: name,
        gender: gender,
        active: true
    };
    
    appData.users.push(newLibrarian);
    saveData();
    
    showSuccess(`Librarian ${name} added successfully! Default password: lib123`);
    document.getElementById('addLibrarianForm').reset();
    showScreen('admin');
}

// Handle add student
function handleAddStudent(e) {
    e.preventDefault();
    
    const name = document.getElementById('studentName').value;
    const studentId = document.getElementById('studentId').value;
    const gender = document.getElementById('studentGender').value;
    const rfid = document.getElementById('studentRfid').value;
    
    // Check for duplicates
    const duplicate = appData.users.find(u => 
        u.active && (u.username === studentId || u.rfid_tag === rfid)
    );
    
    if (duplicate) {
        showError('Student with this Student ID or RFID already exists');
        return;
    }
    
    // Add new student
    const newStudent = {
        id: Date.now(), // Simple ID generation
        username: studentId,
        password: studentId, // Default to student ID
        role: 'student',
        rfid_tag: rfid,
        name: name,
        gender: gender,
        active: true
    };
    
    appData.users.push(newStudent);
    saveData();
    
    showSuccess(`Student ${name} added successfully!`);
    document.getElementById('addStudentForm').reset();
    showScreen('librarian');
}

// Load users table for management
function loadUsersTable() {
    const container = document.getElementById('usersTable');
    const roleFilter = document.getElementById('roleFilter').value;
    const searchTerm = document.getElementById('userSearch').value.toLowerCase();
    
    // Filter users
    let filteredUsers = appData.users.filter(user => user.active);
    
    if (roleFilter !== 'all') {
        filteredUsers = filteredUsers.filter(user => user.role === roleFilter);
    }
    
    if (searchTerm) {
        filteredUsers = filteredUsers.filter(user => 
            user.name.toLowerCase().includes(searchTerm) ||
            user.username.toLowerCase().includes(searchTerm) ||
            user.rfid_tag.toLowerCase().includes(searchTerm)
        );
    }
    
    if (filteredUsers.length > 0) {
        let html = `
            <table>
                <tr>
                    <th>Name</th>
                    <th>Username/ID</th>
                    <th>Role</th>
                    <th>RFID</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
        `;
        
        filteredUsers.forEach(user => {
            const isCurrentUser = currentUser && user.id === currentUser.id;
            const adminCount = appData.users.filter(u => u.role === 'admin' && u.active).length;
            const isLastAdmin = user.role === 'admin' && adminCount === 1;
            
            html += `
                <tr>
                    <td>${user.name || 'N/A'}</td>
                    <td>${user.username}</td>
                    <td>
                        <span class="status-badge ${user.active ? 'status-active' : 'status-inactive'}">
                            ${user.role}
                        </span>
                    </td>
                    <td>${user.rfid_tag}</td>
                    <td>${user.gender || 'N/A'}</td>
                    <td class="action-buttons">
                        ${!isCurrentUser && !isLastAdmin ? 
                            `<button class="btn btn-danger" onclick="confirmDeleteUser(${user.id})">Delete</button>` : 
                            `<button class="btn btn-back" disabled>Cannot Delete</button>`
                        }
                    </td>
                </tr>
            `;
        });
        
        html += '</table>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '<p>No users found matching your criteria.</p>';
    }
}

// Confirm user deletion
function confirmDeleteUser(userId) {
    userToDelete = appData.users.find(user => user.id === userId);
    
    if (!userToDelete) {
        showError('User not found');
        return;
    }
    
    // Check if user has active transactions
    const activeTransactions = appData.transactions.filter(
        t => t.student_rfid === userToDelete.rfid_tag && t.status === 'Borrowed'
    );
    
    let warningMessage = `Are you sure you want to delete user "${userToDelete.name}"?`;
    
    if (activeTransactions.length > 0) {
        warningMessage += `<br><br><strong>Warning:</strong> This user has ${activeTransactions.length} active book transaction(s).`;
    }
    
    // Populate modal
    document.getElementById('deleteUserMessage').innerHTML = warningMessage;
    
    document.getElementById('deleteUserDetails').innerHTML = `
        <p><strong>Username:</strong> ${userToDelete.username}</p>
        <p><strong>Role:</strong> ${userToDelete.role}</p>
        <p><strong>RFID:</strong> ${userToDelete.rfid_tag}</p>
    `;
    
    showModal('deleteUser');
}

// Handle user deletion
function handleDeleteUser() {
    if (!userToDelete) return;
    
    // Soft delete - mark as inactive
    const userIndex = appData.users.findIndex(user => user.id === userToDelete.id);
    if (userIndex !== -1) {
        appData.users[userIndex].active = false;
        saveData();
        
        showSuccess(`User "${userToDelete.name}" has been deleted successfully.`);
        hideModal('deleteUser');
        
        // Reload the users table
        loadUsersTable();
    }
    
    userToDelete = null;
}

// Handle borrow book
function handleBorrowBook(e) {
    e.preventDefault();
    
    const studentRfid = document.getElementById('borrowRfid').value;
    const bookTitle = document.getElementById('borrowBookTitle').value;
    
    // Check if student exists and is active
    const student = appData.users.find(u => 
        u.active && u.rfid_tag === studentRfid && u.role === 'student'
    );
    
    if (!student) {
        showError('Active student with this RFID not found');
        return;
    }
    
    // Create transaction
    const borrowDate = new Date();
    const returnDue = new Date(borrowDate);
    returnDue.setDate(returnDue.getDate() + 3); // Due in 3 days
    
    const newTransaction = {
        id: Date.now(),
        student_rfid: studentRfid,
        book_title: bookTitle,
        borrow_date: borrowDate.toISOString(),
        return_due: returnDue.toISOString(),
        return_date: null,
        status: 'Borrowed',
        penalty: 0
    };
    
    appData.transactions.push(newTransaction);
    saveData();
    
    showSuccess(`Book "${bookTitle}" borrowed successfully by ${student.name}`);
    document.getElementById('borrowBookForm').reset();
    showScreen('librarian');
}

// Handle return book
function handleReturnBook(e) {
    e.preventDefault();
    
    const studentRfid = document.getElementById('returnRfid').value;
    const bookTitle = document.getElementById('returnBookTitle').value;
    
    // Find the transaction
    const transaction = appData.transactions.find(t => 
        t.student_rfid === studentRfid && 
        t.book_title === bookTitle && 
        t.status === 'Borrowed'
    );
    
    if (!transaction) {
        showError('No matching borrow record found');
        return;
    }
    
    // Update transaction
    const returnDate = new Date();
    transaction.return_date = returnDate.toISOString();
    transaction.status = 'Returned';
    
    // Check for late return
    const dueDate = new Date(transaction.return_due);
    if (returnDate > dueDate) {
        const penaltyAmount = 50;
        transaction.penalty = penaltyAmount;
        
        // Add penalty record
        const newPenalty = {
            id: Date.now(),
            student_rfid: studentRfid,
            penalty_amount: penaltyAmount,
            book_title: bookTitle,
            date_recorded: returnDate.toISOString()
        };
        
        appData.penalties.push(newPenalty);
        showSuccess(`Book returned late! Penalty of ₱${penaltyAmount} imposed.`);
    } else {
        showSuccess('Book returned successfully!');
    }
    
    saveData();
    document.getElementById('returnBookForm').reset();
    showScreen('librarian');
}

// Handle change password
function handleChangePassword(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    
    if (newPassword.length < 4) {
        showError('Password must be at least 4 characters long');
        return;
    }
    
    // Update user password
    const userIndex = appData.users.findIndex(u => u.id === currentUser.id);
    if (userIndex !== -1) {
        appData.users[userIndex].password = newPassword;
        saveData();
        showSuccess('Password updated successfully!');
        document.getElementById('changePasswordForm').reset();
        showScreen('librarian');
    }
}

// Show reset password modal
function showResetPasswordModal() {
    loadLibrarianSelect();
    showModal('resetPassword');
}

// Load librarian select for reset password
function loadLibrarianSelect() {
    const select = document.getElementById('librarianSelect');
    select.innerHTML = '<option value="">-- select librarian --</option>';
    
    const librarians = appData.users.filter(u => u.role === 'librarian' && u.active);
    librarians.forEach(lib => {
        const option = document.createElement('option');
        option.value = lib.username;
        option.textContent = lib.name ? `${lib.name} (${lib.username})` : lib.username;
        select.appendChild(option);
    });
}

// Handle reset password
function handleResetPassword() {
    const librarianUsername = document.getElementById('librarianSelect').value;
    
    if (!librarianUsername) {
        showError('Please select a librarian');
        return;
    }
    
    // Reset password to default
    const userIndex = appData.users.findIndex(u => 
        u.active && u.username === librarianUsername && u.role === 'librarian'
    );
    
    if (userIndex !== -1) {
        appData.users[userIndex].password = 'lib123';
        saveData();
        
        const librarian = appData.users[userIndex];
        showSuccess(`Password for ${librarian.name || librarian.username} reset to: lib123`);
        hideModal('resetPassword');
    }
}

// Load student data for student dashboard
function loadStudentData() {
    if (!currentUser || currentUser.role !== 'student') return;
    
    // Load transactions
    const studentTransactions = appData.transactions.filter(t => 
        t.student_rfid === currentUser.rfid_tag
    );
    
    const transactionsContainer = document.getElementById('studentTransactions');
    if (studentTransactions.length > 0) {
        let html = `
            <table>
                <tr>
                    <th>Book Title</th>
                    <th>Status</th>
                    <th>Borrowed On</th>
                    <th>Returned On</th>
                </tr>
        `;
        
        studentTransactions.forEach(t => {
            html += `
                <tr>
                    <td>${t.book_title}</td>
                    <td>${t.status}</td>
                    <td>${formatDate(t.borrow_date)}</td>
                    <td>${t.return_date ? formatDate(t.return_date) : 'Not yet returned'}</td>
                </tr>
            `;
        });
        
        html += '</table>';
        transactionsContainer.innerHTML = html;
    } else {
        transactionsContainer.innerHTML = '<p>No borrow history found.</p>';
    }
    
    // Load penalties
    const studentPenalties = appData.penalties.filter(p => 
        p.student_rfid === currentUser.rfid_tag
    );
    
    const penaltiesContainer = document.getElementById('studentPenalties');
    if (studentPenalties.length > 0) {
        let html = `
            <table>
                <tr>
                    <th>Penalty Amount (PHP)</th>
                    <th>Book Title</th>
                    <th>Date Recorded</th>
                </tr>
        `;
        
        studentPenalties.forEach(p => {
            html += `
                <tr>
                    <td>₱${p.penalty_amount.toFixed(2)}</td>
                    <td>${p.book_title}</td>
                    <td>${formatDate(p.date_recorded)}</td>
                </tr>
            `;
        });
        
        html += '</table>';
        penaltiesContainer.innerHTML = html;
    } else {
        penaltiesContainer.innerHTML = '<p>You have no pending penalties 🎉</p>';
    }
}

// Load transactions table
function loadTransactionsTable() {
    const container = document.getElementById('transactionsTable');
    
    if (appData.transactions.length > 0) {
        let html = `
            <table>
                <tr>
                    <th>Student RFID</th>
                    <th>Book Title</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Penalty</th>
                </tr>
        `;
        
        // Sort by most recent first
        const sortedTransactions = [...appData.transactions].sort((a, b) => 
            new Date(b.borrow_date) - new Date(a.borrow_date)
        );
        
        sortedTransactions.forEach(t => {
            const isLate = t.status === 'Borrowed' && new Date() > new Date(t.return_due);
            const rowClass = isLate ? 'class="late-row"' : '';
            
            html += `
                <tr ${rowClass}>
                    <td>${t.student_rfid}</td>
                    <td>${t.book_title}</td>
                    <td>${formatDate(t.borrow_date)}</td>
                    <td>${formatDate(t.return_due)}</td>
                    <td>${t.return_date ? formatDate(t.return_date) : 'Not yet returned'}</td>
                    <td>${t.status}</td>
                    <td>₱${t.penalty.toFixed(2)}</td>
                </tr>
            `;
        });
        
        html += '</table>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '<p>No transactions found.</p>';
    }
    
    // Add CSS for late rows
    if (!document.querySelector('#late-style')) {
        const style = document.createElement('style');
        style.id = 'late-style';
        style.textContent = '.late-row { background-color: #f8d7da !important; }';
        document.head.appendChild(style);
    }
}

// Load logs table
function loadLogsTable() {
    const container = document.getElementById('logsTable');
    
    if (appData.transactions.length > 0) {
        let html = `
            <table>
                <tr>
                    <th>Student RFID</th>
                    <th>Book Title</th>
                    <th>Borrowed On</th>
                    <th>Due Date</th>
                    <th>Returned On</th>
                    <th>Status</th>
                    <th>Penalty</th>
                </tr>
        `;
        
        // Sort by most recent first
        const sortedTransactions = [...appData.transactions].sort((a, b) => 
            new Date(b.borrow_date) - new Date(a.borrow_date)
        );
        
        sortedTransactions.forEach(t => {
            html += `
                <tr>
                    <td>${t.student_rfid}</td>
                    <td>${t.book_title}</td>
                    <td>${formatDate(t.borrow_date)}</td>
                    <td>${formatDate(t.return_due)}</td>
                    <td>${t.return_date ? formatDate(t.return_date) : 'N/A'}</td>
                    <td>${t.status}</td>
                    <td>₱${t.penalty.toFixed(2)}</td>
                </tr>
            `;
        });
        
        html += '</table>';
        container.innerHTML = html;
    } else {
        container.innerHTML = '<p>No transaction logs found.</p>';
    }
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}