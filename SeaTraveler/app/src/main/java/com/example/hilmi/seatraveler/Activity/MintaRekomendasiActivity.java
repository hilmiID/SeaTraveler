package com.example.hilmi.seatraveler.Activity;

import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.net.ConnectivityManager;
import android.os.Bundle;
import android.support.v4.app.ActivityCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.Toast;

import com.example.hilmi.seatraveler.R;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.tasks.OnSuccessListener;

public class MintaRekomendasiActivity extends AppCompatActivity {

    private Button proses;
    private EditText jarak, htm, rating, fasilitas, transportasi;
    private ImageButton backButton;
    public ConnectivityManager conMgr;
    private FusedLocationProviderClient myLocation;
    private static final int LOCATION_REQUEST = 500;
    private String latitude, longitude;

    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_minta_rekomendasi);

        conMgr = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);

        //get latitude and longitude from GPS
        myLocation = LocationServices.getFusedLocationProviderClient(MintaRekomendasiActivity.this);

        if (ActivityCompat.checkSelfPermission(MintaRekomendasiActivity.this, android.Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(MintaRekomendasiActivity.this, android.Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            ActivityCompat.requestPermissions(MintaRekomendasiActivity.this, new String[]{android.Manifest.permission.ACCESS_FINE_LOCATION}, LOCATION_REQUEST);
            return;
        }

        myLocation.getLastLocation().addOnSuccessListener(MintaRekomendasiActivity.this, new OnSuccessListener<Location>() {
            @Override
            public void onSuccess(Location location) {
                latitude = String.valueOf(location.getLatitude());
                longitude = String.valueOf(location.getLongitude());
            }
        });

        jarak = findViewById(R.id.editTextJarak);
        rating = findViewById(R.id.editTextRating);
        htm = findViewById(R.id.editTextHTM);
        fasilitas = findViewById(R.id.editTextFasilitas);
        transportasi = findViewById(R.id.editTextTransportasi);
        proses = findViewById(R.id.bottom_button);
        proses.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String valueJarak = jarak.getText().toString();
                String valueHTM = htm.getText().toString();
                String valueRating = rating.getText().toString();
                String valueFasilitas = fasilitas.getText().toString();
                String valueTransportasi = transportasi.getText().toString();

                // mengecek kolom yang kosong
                if (valueJarak.trim().length() > 0 && valueHTM.trim().length() > 0 && valueRating.trim().length() > 0 && valueFasilitas.trim().length() > 0 && valueTransportasi.trim().length() > 0) {
                    if (conMgr.getActiveNetworkInfo() != null && conMgr.getActiveNetworkInfo().isAvailable() && conMgr.getActiveNetworkInfo().isConnected()) {
                        Intent intent = new Intent(MintaRekomendasiActivity.this, HasilRekomendasiActivity.class);
                        intent.putExtra("valueJarak", valueJarak);
                        intent.putExtra("valueHTM", valueHTM);
                        intent.putExtra("valueRating", valueRating);
                        intent.putExtra("valueFasilitas", valueFasilitas);
                        intent.putExtra("valueTransportasi", valueTransportasi);
                        intent.putExtra("latitude", latitude);
                        intent.putExtra("longitude", longitude);
                        startActivity(intent);
                    } else {
                        Toast.makeText(getApplicationContext() ,"No Internet Connection", Toast.LENGTH_LONG).show();
                    }
                } else {
                    // Prompt user to enter credentials
                    Toast.makeText(getApplicationContext() ,"Kolom tidak boleh kosong", Toast.LENGTH_LONG).show();
                }
            }
        });

        backButton = findViewById(R.id.backButton);
        backButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
    }
}
