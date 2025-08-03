#!/bin/bash

# ENNU Life Assessments - Terminal Monitoring Script
# Comprehensive monitoring for logs, database, and system health

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
WORDPRESS_ROOT="/Applications/MAMP/htdocs"
DEBUG_LOG="$WORDPRESS_ROOT/wp-content/debug.log"
PLUGIN_DIR="$WORDPRESS_ROOT/wp-content/plugins/ennulifeassessments"
MAMP_MYSQL="/Applications/MAMP/Library/bin/mysql"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Function to check if MAMP is running
check_mamp_status() {
    print_status "Checking MAMP status..."
    
    if pgrep -f "MAMP" > /dev/null; then
        print_success "MAMP is running"
        return 0
    else
        print_error "MAMP is not running"
        return 1
    fi
}

# Function to monitor debug log
monitor_debug_log() {
    print_status "Monitoring debug log for ENNU-related entries..."
    
    if [ ! -f "$DEBUG_LOG" ]; then
        print_warning "Debug log not found at $DEBUG_LOG"
        return
    fi
    
    # Show last 20 ENNU-related entries
    echo "=== Last 20 ENNU Debug Entries ==="
    grep -i "ennu" "$DEBUG_LOG" | tail -20 | while read -r line; do
        echo "$line"
    done
    echo "=================================="
}

# Function to check plugin files
check_plugin_files() {
    print_status "Checking ENNU plugin files..."
    
    local missing_files=()
    
    # Check essential files
    local essential_files=(
        "ennu-life-plugin.php"
        "includes/class-assessment-shortcodes.php"
        "templates/assessment-results.php"
        "assets/css/user-dashboard.css"
    )
    
    for file in "${essential_files[@]}"; do
        if [ ! -f "$PLUGIN_DIR/$file" ]; then
            missing_files+=("$file")
        fi
    done
    
    if [ ${#missing_files[@]} -eq 0 ]; then
        print_success "All essential plugin files present"
    else
        print_error "Missing files:"
        for file in "${missing_files[@]}"; do
            echo "  - $file"
        done
    fi
}

# Function to check database connectivity
check_database() {
    print_status "Checking database connectivity..."
    
    # Try to connect to MySQL
    if command -v mysql >/dev/null 2>&1; then
        if mysql -u root -p -e "SELECT 1;" 2>/dev/null; then
            print_success "Database connection successful"
            return 0
        else
            print_error "Database connection failed"
            return 1
        fi
    else
        print_warning "MySQL client not found in PATH"
        return 1
    fi
}

# Function to check ENNU database tables
check_ennu_database() {
    print_status "Checking ENNU database tables..."
    
    # Check for ENNU user meta entries
    local query="SELECT COUNT(*) as count FROM wp_usermeta WHERE meta_key LIKE 'ennu_%';"
    
    if command -v mysql >/dev/null 2>&1; then
        local count=$(mysql -u root -p -e "$query" 2>/dev/null | tail -1)
        if [ "$count" -ge 0 ]; then
            print_success "Found $count ENNU user meta entries"
        else
            print_warning "No ENNU user meta entries found"
        fi
    else
        print_warning "Cannot check database - MySQL client not available"
    fi
}

# Function to monitor system resources
monitor_system_resources() {
    print_status "Monitoring system resources..."
    
    # CPU usage
    local cpu_usage=$(top -l 1 | grep "CPU usage" | awk '{print $3}' | sed 's/%//')
    echo "CPU Usage: ${cpu_usage}%"
    
    # Memory usage
    local memory_info=$(vm_stat | grep "Pages free" | awk '{print $3}' | sed 's/\.//')
    local total_memory=$(sysctl hw.memsize | awk '{print $2}')
    local free_memory=$((memory_info * 4096))
    local used_memory=$((total_memory - free_memory))
    local memory_percent=$((used_memory * 100 / total_memory))
    
    echo "Memory Usage: ${memory_percent}%"
    
    # Disk usage for WordPress directory
    local disk_usage=$(df "$WORDPRESS_ROOT" | tail -1 | awk '{print $5}' | sed 's/%//')
    echo "Disk Usage (WordPress): ${disk_usage}%"
}

# Function to check for recent errors
check_recent_errors() {
    print_status "Checking for recent errors..."
    
    if [ ! -f "$DEBUG_LOG" ]; then
        print_warning "Debug log not found"
        return
    fi
    
    # Check for errors in last hour
    local recent_errors=$(grep -i "error\|fatal\|exception" "$DEBUG_LOG" | grep "$(date '+%Y-%m-%d %H:')" | wc -l)
    
    if [ "$recent_errors" -gt 0 ]; then
        print_warning "Found $recent_errors errors in the last hour"
        echo "Recent errors:"
        grep -i "error\|fatal\|exception" "$DEBUG_LOG" | grep "$(date '+%Y-%m-%d %H:')" | tail -5
    else
        print_success "No recent errors found"
    fi
}

# Function to check plugin activation
check_plugin_activation() {
    print_status "Checking plugin activation status..."
    
    # Check if plugin is active in WordPress
    local plugin_active=$(grep -r "ennulifeassessments" "$WORDPRESS_ROOT/wp-content/plugins/" 2>/dev/null | wc -l)
    
    if [ "$plugin_active" -gt 0 ]; then
        print_success "Plugin files found in WordPress plugins directory"
    else
        print_error "Plugin files not found in WordPress plugins directory"
    fi
}

# Function to monitor real-time logs
monitor_realtime_logs() {
    print_status "Starting real-time log monitoring (Ctrl+C to stop)..."
    echo "Monitoring for ENNU-related entries..."
    
    if [ -f "$DEBUG_LOG" ]; then
        tail -f "$DEBUG_LOG" | grep --line-buffered -i "ennu"
    else
        print_error "Debug log not found"
    fi
}

# Function to show help
show_help() {
    echo "ENNU Life Assessments - Terminal Monitoring Script"
    echo ""
    echo "Usage: $0 [OPTION]"
    echo ""
    echo "Options:"
    echo "  status      Show overall system status"
    echo "  logs        Monitor debug logs"
    echo "  database    Check database connectivity and ENNU data"
    echo "  files       Check plugin files"
    echo "  resources   Monitor system resources"
    echo "  errors      Check for recent errors"
    echo "  realtime    Monitor logs in real-time"
    echo "  all         Run all checks"
    echo "  help        Show this help message"
    echo ""
}

# Main function
main() {
    case "${1:-all}" in
        "status")
            check_mamp_status
            check_plugin_activation
            ;;
        "logs")
            monitor_debug_log
            ;;
        "database")
            check_database
            check_ennu_database
            ;;
        "files")
            check_plugin_files
            ;;
        "resources")
            monitor_system_resources
            ;;
        "errors")
            check_recent_errors
            ;;
        "realtime")
            monitor_realtime_logs
            ;;
        "all")
            echo "=== ENNU Life Assessments - System Monitoring ==="
            echo ""
            check_mamp_status
            echo ""
            check_plugin_activation
            echo ""
            check_plugin_files
            echo ""
            check_database
            check_ennu_database
            echo ""
            monitor_system_resources
            echo ""
            check_recent_errors
            echo ""
            monitor_debug_log
            echo ""
            echo "=== Monitoring Complete ==="
            ;;
        "help"|"-h"|"--help")
            show_help
            ;;
        *)
            print_error "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
}

# Run main function with all arguments
main "$@" 