/**
 * RV BUILDERS - API Client
 * JavaScript client for connecting frontend to backend API
 */

const API = {
    // Base URL - Update this to match your server configuration
    baseUrl: 'http://localhost/RV-Builders/Backend/api',

    /**
     * Make API request
     * @param {string} endpoint - API endpoint
     * @param {object} options - Fetch options
     * @returns {Promise<object>} Response data
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;

        const defaultOptions = {
            headers: {
                'Accept': 'application/json',
            },
        };

        // Don't set Content-Type for FormData (browser will set it with boundary)
        if (!(options.body instanceof FormData)) {
            defaultOptions.headers['Content-Type'] = 'application/json';
        }

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * Projects API
     */
    projects: {
        /**
         * Get all projects
         * @param {object} params - Query parameters (category, limit, offset)
         * @returns {Promise<object>}
         */
        async getAll(params = {}) {
            const queryString = new URLSearchParams(params).toString();
            const endpoint = queryString ? `projects.php?${queryString}` : 'projects.php';
            return API.request(endpoint);
        },

        /**
         * Get single project by ID
         * @param {number} id - Project ID
         * @returns {Promise<object>}
         */
        async getById(id) {
            return API.request(`projects.php?id=${id}`);
        },

        /**
         * Get featured projects
         * @param {number} limit - Number of projects
         * @returns {Promise<object>}
         */
        async getFeatured(limit = 6) {
            return API.request(`projects.php?action=featured&limit=${limit}`);
        },

        /**
         * Create new project
         * @param {FormData} formData - Project data with images
         * @returns {Promise<object>}
         */
        async create(formData) {
            return API.request('projects.php', {
                method: 'POST',
                body: formData
            });
        },

        /**
         * Update project
         * @param {number} id - Project ID
         * @param {FormData} formData - Project data
         * @returns {Promise<object>}
         */
        async update(id, formData) {
            return API.request(`projects.php?id=${id}`, {
                method: 'POST', // Using POST for file uploads
                body: formData
            });
        },

        /**
         * Delete project
         * @param {number} id - Project ID
         * @returns {Promise<object>}
         */
        async delete(id) {
            return API.request(`projects.php?id=${id}`, {
                method: 'DELETE'
            });
        },

        /**
         * Add images to project
         * @param {number} projectId - Project ID
         * @param {FormData} formData - Images
         * @returns {Promise<object>}
         */
        async addImages(projectId, formData) {
            return API.request(`projects.php?id=${projectId}&action=images`, {
                method: 'POST',
                body: formData
            });
        },

        /**
         * Delete image from project
         * @param {number} imageId - Image ID
         * @returns {Promise<object>}
         */
        async deleteImage(imageId) {
            return API.request(`projects.php?action=image&image_id=${imageId}`, {
                method: 'DELETE'
            });
        }
    },

    /**
     * Categories API
     */
    categories: {
        /**
         * Get all categories
         * @returns {Promise<object>}
         */
        async getAll() {
            return API.request('categories.php');
        },

        /**
         * Get category by slug
         * @param {string} slug - Category slug
         * @returns {Promise<object>}
         */
        async getBySlug(slug) {
            return API.request(`categories.php?slug=${slug}`);
        }
    }
};

/**
 * Project Card HTML Generator
 * Generates HTML for project cards from API data
 */
const ProjectRenderer = {
    /**
     * Render project card
     * @param {object} project - Project data from API
     * @returns {string} HTML string
     */
    renderCard(project) {
        const primaryImage = project.images && project.images.length > 0
            ? project.images.find(img => img.is_primary) || project.images[0]
            : null;

        const imageUrl = primaryImage
            ? primaryImage.image_url
            : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';

        return `
            <div class="professional-project-card" data-category="${project.category}" data-project-id="${project.id}">
                <div class="project-image-wrapper">
                    <img src="${imageUrl}" alt="${project.title}" loading="lazy">
                    <div class="project-overlay">
                        <div class="project-category-badge">${this.formatCategory(project.category)}</div>
                        <a href="#project${project.id}-popup" class="project-view-btn">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                <div class="project-info">
                    <h3 class="project-title">${project.title}</h3>
                    <p class="project-location">
                        <i class="fas fa-map-marker-alt"></i> ${project.location || 'Tamil Nadu, India'}
                    </p>
                </div>
            </div>
        `;
    },

    /**
     * Render project popup
     * @param {object} project - Project data from API
     * @returns {string} HTML string
     */
    renderPopup(project) {
        const images = project.images || [];
        const primaryImage = images.find(img => img.is_primary) || images[0];
        const imageUrl = primaryImage
            ? primaryImage.image_url
            : 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';

        // Generate image gallery if multiple images
        let galleryHtml = '';
        if (images.length > 1) {
            galleryHtml = `
                <div class="project-gallery">
                    ${images.map((img, index) => `
                        <img src="${img.image_url}"
                             alt="${project.title} - Image ${index + 1}"
                             class="gallery-thumb ${img.is_primary ? 'active' : ''}"
                             onclick="ProjectRenderer.setMainImage(this, '${img.image_url}')">
                    `).join('')}
                </div>
            `;
        }

        return `
            <div class="popup" id="project${project.id}-popup">
                <div class="popup-content">
                    <div class="popup-header">
                        <div>
                            <h2 class="popup-title">${project.title}</h2>
                            <p>${this.formatCategory(project.category)} Project</p>
                        </div>
                        <button class="popup-close">&times;</button>
                    </div>
                    <div class="popup-body">
                        <div class="popup-image">
                            <img src="${imageUrl}" alt="${project.title}" id="main-image-${project.id}">
                        </div>
                        ${galleryHtml}

                        <p>${project.description || 'A quality construction project by RV BUILDERS.'}</p>

                        <div class="project-details">
                            <div class="detail-item">
                                <h4>Client</h4>
                                <p>${project.client_name || 'RV BUILDERS Client'}</p>
                            </div>
                            <div class="detail-item">
                                <h4>Location</h4>
                                <p>${project.location || 'Tamil Nadu, India'}</p>
                            </div>
                            <div class="detail-item">
                                <h4>Status</h4>
                                <p>${this.formatStatus(project.completion_status)}</p>
                            </div>
                        </div>

                        <div class="popup-cta">
                            <a href="contact.html" class="btn btn-primary">
                                <i class="fas fa-phone-alt"></i> Discuss Your Project
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    },

    /**
     * Format category name
     * @param {string} category - Category slug
     * @returns {string} Formatted name
     */
    formatCategory(category) {
        const categories = {
            'residential': 'Residential',
            'commercial': 'Commercial',
            'renovation': 'Renovation',
            'interior': 'Interior'
        };
        return categories[category] || category.charAt(0).toUpperCase() + category.slice(1);
    },

    /**
     * Format status
     * @param {string} status - Status value
     * @returns {string} Formatted status
     */
    formatStatus(status) {
        const statuses = {
            'completed': 'Completed',
            'in_progress': 'In Progress',
            'upcoming': 'Upcoming'
        };
        return statuses[status] || status;
    },

    /**
     * Set main image in popup gallery
     * @param {HTMLElement} thumb - Clicked thumbnail
     * @param {string} imageUrl - Image URL
     */
    setMainImage(thumb, imageUrl) {
        // Update main image
        const popup = thumb.closest('.popup');
        const mainImage = popup.querySelector('.popup-image img');
        mainImage.src = imageUrl;

        // Update active state
        popup.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
    }
};

/**
 * Initialize projects from API
 * Call this function to load projects dynamically
 */
async function loadProjectsFromAPI() {
    try {
        const container = document.querySelector('.projects-grid');
        const popupContainer = document.querySelector('.project-popups') || document.body;

        if (!container) return;

        // Show loading state
        container.innerHTML = '<div class="loading">Loading projects...</div>';

        // Fetch projects from API
        const response = await API.projects.getAll();

        if (response.success && response.data) {
            // Clear container
            container.innerHTML = '';

            // Render each project
            response.data.forEach(project => {
                // Add card
                container.insertAdjacentHTML('beforeend', ProjectRenderer.renderCard(project));

                // Add popup
                popupContainer.insertAdjacentHTML('beforeend', ProjectRenderer.renderPopup(project));
            });

            // Re-initialize popup handlers
            initPopupHandlers();

            // Re-initialize filter if exists
            if (typeof initProjectFilters === 'function') {
                initProjectFilters();
            }
        } else {
            container.innerHTML = '<div class="error">Failed to load projects</div>';
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        const container = document.querySelector('.projects-grid');
        if (container) {
            container.innerHTML = '<div class="error">Failed to load projects. Please try again later.</div>';
        }
    }
}

/**
 * Initialize popup handlers for dynamically loaded content
 */
function initPopupHandlers() {
    // Close popup buttons
    document.querySelectorAll('.popup-close').forEach(button => {
        button.addEventListener('click', function() {
            const popup = this.closest('.popup');
            if (popup) {
                popup.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Close popup on background click
    document.querySelectorAll('.popup').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Project card click handlers
    document.querySelectorAll('.professional-project-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('.project-view-btn')) return;

            const projectId = this.getAttribute('data-project-id');
            const popup = document.getElementById(`project${projectId}-popup`);
            if (popup) {
                popup.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // View button handlers
    document.querySelectorAll('.project-view-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const href = this.getAttribute('href');
            if (href && href.startsWith('#')) {
                const popup = document.getElementById(href.substring(1));
                if (popup) {
                    popup.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }
        });
    });
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API, ProjectRenderer, loadProjectsFromAPI };
}
