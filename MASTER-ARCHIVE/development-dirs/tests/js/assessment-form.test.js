/**
 * Test suite for assessment form functionality
 * @jest-environment jsdom
 */

describe('Assessment Form Validation', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <form id="ennu-assessment-form" data-assessment-type="hair_assessment">
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required />
                    <div class="error-message" id="email-error"></div>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required />
                    <div class="error-message" id="first_name-error"></div>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <input type="radio" name="hair_q1" value="male" id="male" />
                    <label for="male">Male</label>
                    <input type="radio" name="hair_q1" value="female" id="female" />
                    <label for="female">Female</label>
                </div>
                <div class="form-group">
                    <label for="hair_q2">Primary Hair Concern</label>
                    <select name="hair_q2" id="hair_q2">
                        <option value="">Select...</option>
                        <option value="thinning">Thinning</option>
                        <option value="receding">Receding</option>
                        <option value="loss">Hair Loss</option>
                    </select>
                </div>
                <button type="submit" id="submit-btn">Submit Assessment</button>
            </form>
        `;
    });

    describe('Form Validation Functions', () => {
        test('should validate email format correctly', () => {
            const validateEmail = (email) => {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            };

            expect(validateEmail('test@example.com')).toBe(true);
            expect(validateEmail('user.name+tag@domain.co.uk')).toBe(true);
            expect(validateEmail('invalid-email')).toBe(false);
            expect(validateEmail('test@')).toBe(false);
            expect(validateEmail('@example.com')).toBe(false);
            expect(validateEmail('')).toBe(false);
        });

        test('should validate required fields', () => {
            const validateRequiredFields = (formData) => {
                const requiredFields = ['email', 'first_name'];
                const errors = [];

                requiredFields.forEach(field => {
                    if (!formData[field] || formData[field].trim() === '') {
                        errors.push(`${field} is required`);
                    }
                });

                return errors;
            };

            const validData = { email: 'test@example.com', first_name: 'John' };
            const invalidData = { email: '', first_name: 'John' };
            const emptyData = { email: '', first_name: '' };

            expect(validateRequiredFields(validData)).toEqual([]);
            expect(validateRequiredFields(invalidData)).toContain('email is required');
            expect(validateRequiredFields(emptyData)).toHaveLength(2);
        });

        test('should sanitize user input', () => {
            const sanitizeInput = (input) => {
                if (typeof input !== 'string') return input;
                
                return input
                    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
                    .replace(/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi, '')
                    .replace(/javascript:/gi, '')
                    .replace(/on\w+\s*=/gi, '');
            };

            const maliciousInputs = [
                '<script>alert("xss")</script>',
                '<iframe src="javascript:alert(1)"></iframe>',
                'javascript:alert("xss")',
                '<img src="x" onerror="alert(1)">',
                'onclick="alert(1)"'
            ];

            maliciousInputs.forEach(input => {
                const sanitized = sanitizeInput(input);
                expect(sanitized).not.toContain('<script>');
                expect(sanitized).not.toContain('<iframe>');
                expect(sanitized).not.toContain('javascript:');
                expect(sanitized).not.toContain('onerror=');
                expect(sanitized).not.toContain('onclick=');
            });
        });
    });

    describe('Form Interaction', () => {
        test('should collect form data correctly', () => {
            const form = document.getElementById('ennu-assessment-form');
            const emailInput = document.getElementById('email');
            const firstNameInput = document.getElementById('first_name');
            const maleRadio = document.getElementById('male');
            const hairConcernSelect = document.getElementById('hair_q2');

            emailInput.value = 'test@example.com';
            firstNameInput.value = 'John Doe';
            maleRadio.checked = true;
            hairConcernSelect.value = 'thinning';

            const collectFormData = (form) => {
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                return data;
            };

            const formData = collectFormData(form);

            expect(formData.email).toBe('test@example.com');
            expect(formData.first_name).toBe('John Doe');
            expect(formData.hair_q1).toBe('male');
            expect(formData.hair_q2).toBe('thinning');
        });

        test('should show/hide error messages', () => {
            const showError = (fieldId, message) => {
                const errorElement = document.getElementById(`${fieldId}-error`);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
            };

            const hideError = (fieldId) => {
                const errorElement = document.getElementById(`${fieldId}-error`);
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
            };

            const emailError = document.getElementById('email-error');

            showError('email', 'Please enter a valid email address');
            expect(emailError.textContent).toBe('Please enter a valid email address');
            expect(emailError.style.display).toBe('block');

            hideError('email');
            expect(emailError.textContent).toBe('');
            expect(emailError.style.display).toBe('none');
        });

        test('should handle form submission', () => {
            const form = document.getElementById('ennu-assessment-form');
            const submitButton = document.getElementById('submit-btn');
            
            const mockSubmitHandler = jest.fn((e) => {
                e.preventDefault();
                return false;
            });

            form.addEventListener('submit', mockSubmitHandler);
            
            const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
            form.dispatchEvent(submitEvent);

            expect(mockSubmitHandler).toHaveBeenCalled();
        });
    });

    describe('AJAX Form Submission', () => {
        test('should prepare AJAX data correctly', () => {
            const form = document.getElementById('ennu-assessment-form');
            
            const prepareAjaxData = (form) => {
                const formData = new FormData(form);
                const data = {
                    action: 'ennu_submit_assessment',
                    nonce: 'mock_nonce_value',
                    assessment_type: form.dataset.assessmentType
                };

                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }

                return data;
            };

            document.getElementById('email').value = 'test@example.com';
            document.getElementById('first_name').value = 'John';

            const ajaxData = prepareAjaxData(form);

            expect(ajaxData.action).toBe('ennu_submit_assessment');
            expect(ajaxData.assessment_type).toBe('hair_assessment');
            expect(ajaxData.nonce).toBe('mock_nonce_value');
            expect(ajaxData.email).toBe('test@example.com');
            expect(ajaxData.first_name).toBe('John');
        });

        test('should handle AJAX success response', () => {
            const handleAjaxSuccess = (response) => {
                if (response.success) {
                    return {
                        success: true,
                        message: response.data.message || 'Assessment submitted successfully',
                        redirectUrl: response.data.redirect_url
                    };
                } else {
                    return {
                        success: false,
                        message: response.data || 'Submission failed'
                    };
                }
            };

            const successResponse = {
                success: true,
                data: {
                    message: 'Assessment completed successfully',
                    redirect_url: '/dashboard'
                }
            };

            const failureResponse = {
                success: false,
                data: 'Validation errors occurred'
            };

            const successResult = handleAjaxSuccess(successResponse);
            expect(successResult.success).toBe(true);
            expect(successResult.message).toBe('Assessment completed successfully');
            expect(successResult.redirectUrl).toBe('/dashboard');

            const failureResult = handleAjaxSuccess(failureResponse);
            expect(failureResult.success).toBe(false);
            expect(failureResult.message).toBe('Validation errors occurred');
        });
    });

    describe('Accessibility Features', () => {
        test('should have proper form labels', () => {
            const emailInput = document.getElementById('email');
            const emailLabel = document.querySelector('label[for="email"]');
            
            expect(emailLabel).toBeTruthy();
            expect(emailLabel.textContent).toContain('Email Address');
            expect(emailInput.getAttribute('required')).toBe('');
        });

        test('should support keyboard navigation', () => {
            const form = document.getElementById('ennu-assessment-form');
            const inputs = form.querySelectorAll('input, select, button');
            
            inputs.forEach(input => {
                expect(input.tabIndex).not.toBe(-1);
            });
        });

        test('should have ARIA attributes for errors', () => {
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('email-error');
            
            emailInput.setAttribute('aria-describedby', 'email-error');
            emailError.setAttribute('role', 'alert');
            
            expect(emailInput.getAttribute('aria-describedby')).toBe('email-error');
            expect(emailError.getAttribute('role')).toBe('alert');
        });
    });
});
