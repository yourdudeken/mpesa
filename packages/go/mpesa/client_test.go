package mpesa

import (
	"testing"
)

func TestConfigCreation(t *testing.T) {
	config := &Config{
		Environment:         "sandbox",
		MpesaConsumerKey:    "test_key",
		MpesaConsumerSecret: "test_secret",
		Passkey:             "test_passkey",
		Shortcode:           "174379",
		InitiatorName:       "testapi",
		InitiatorPassword:   "test_password",
	}

	if config.Environment != "sandbox" {
		t.Errorf("expected sandbox, got %s", config.Environment)
	}
	if config.MpesaConsumerKey != "test_key" {
		t.Errorf("expected test_key, got %s", config.MpesaConsumerKey)
	}
}

func TestMpesaClientCreation(t *testing.T) {
	config := &Config{
		Environment:         "sandbox",
		MpesaConsumerKey:    "test_key",
		MpesaConsumerSecret: "test_secret",
		Passkey:             "test_passkey",
		Shortcode:           "174379",
		InitiatorName:       "testapi",
		InitiatorPassword:   "test_password",
	}

	client := NewClient(config)
	if client == nil {
		t.Error("expected client, got nil")
	}
}

func TestHelpersPhoneValidator(t *testing.T) {
	helpers := NewHelpers(&Config{})

	tests := []struct {
		input    string
		expected string
	}{
		{"+254712345678", "254712345678"},
		{"0712345678", "254712345678"},
		{"712345678", "254712345678"},
	}

	for _, tt := range tests {
		result := helpers.PhoneValidator(tt.input)
		if result != tt.expected {
			t.Errorf("PhoneValidator(%s) = %s; want %s", tt.input, result, tt.expected)
		}
	}
}

func TestHelpersGetFormattedTimestamp(t *testing.T) {
	helpers := &Helpers{}
	result := helpers.GetFormattedTimestamp()

	if len(result) != 14 {
		t.Errorf("expected 14 characters, got %d", len(result))
	}
}
